<?php


namespace App\Services\Onlyoffice;


use App\Domain\Project\Entity\Conclusion\Conclusion;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Block;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\Kind;
use App\Domain\Project\Entity\Conclusion\Paragraph\Block\State;
use App\Domain\Project\Entity\Conclusion\Paragraph\Paragraph;
use App\Domain\Project\Entity\Dictionary\Key;
use App\Domain\Project\Service\DictionaryKeyTranslator;
use App\Http\ReadModel\BlockDictionariesFetcher;
use App\Services\ServicesUrlManager;
use App\Services\SiteEnvResolver;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;

class FinalDocxGenerator
{

    private string $conclusionsServiceUrl;
    private Client $client;
    private ?string $host;
    private DictionaryKeyTranslator $translator;
    private LoggerInterface $logger;
    private BlockDictionariesFetcher $dictionaries;

    public function __construct(
        ServicesUrlManager $urlManager,
        SiteEnvResolver $resolver,
        DictionaryKeyTranslator $translator,
        BlockDictionariesFetcher $dictionaries,
        LoggerInterface $logger,
        Client $client)
    {
        $this->conclusionsServiceUrl = $urlManager->conclusionsServiceUrl();
        $this->client = $client;
        $this->host = $resolver->resolve();
        $this->dictionaries = $dictionaries;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function generate(Conclusion $conclusion): DocxGenerationResult
    {
        $savePath = "{$conclusion->getProject()->getId()}/{$conclusion->getId()}/print_form.docx";
        if ($this->host) {
            $savePath = "{$this->host}/$savePath";
        } else {
            $savePath = "localhost/$savePath";
        }

        $randomUuid = Uuid::uuid4()->toString();
        $key = "{$this->host}:{$randomUuid}";

        $data = [
            'main_header_number' => (string)$conclusion->getTitle(),
            'content' => $this->mapParagraphs($conclusion->getRootParagraphs()),
            'key' => $key,
            'save_path' => $savePath,
        ];

        $url = "{$this->conclusionsServiceUrl}/reports/generate";

        $rawResponse = $this->client->post($url, [
            RequestOptions::JSON => $data,
            RequestOptions::TIMEOUT => 600,
        ]);

        if ($rawResponse->getStatusCode() !== 201) {
            $this->logger->error('Error creation print form', [
                'request_data' => $data,
                'response_body' => $rawResponse->getBody()->getContents(),
                'response_code' => $rawResponse->getStatusCode(),
                'response_reason' => $rawResponse->getReasonPhrase(),
            ]);
            throw new RuntimeException('Ошибка создания печатной формы.');
        }

        $savedPath = $rawResponse->getBody()->getContents();

        return new DocxGenerationResult($savedPath, $key);
    }

    /**
     * @param Paragraph[] $paragraphs
     * @return array
     */
    protected function mapParagraphs(iterable $paragraphs): array
    {
        /** @var Paragraph[] $arrParagraphs */
        $arrParagraphs = [];
        array_push($arrParagraphs, ...$paragraphs);
        usort($arrParagraphs, function (Paragraph $first, Paragraph $second){
            return $first->getOrder()->getValue() > $second->getOrder()->getValue();
        });

        $resultItems = [];
        foreach ($arrParagraphs as $paragraph) {
            $hierarchyOrder = $this->getParagraphHierarchyOrder($paragraph);
            $resultItem = [
                'title' => "{$hierarchyOrder}. {$paragraph->getTitle()->getValue()}",
                'items' => [],
            ];

            /** @var Block[] $blocks */
            $blocks = [...$paragraph->getBlocks()->getValues()];
            $blocks = array_filter($blocks, function (Block $b) {
                return $b->getState()->getValue() !== State::DELETED;
            });
            usort($blocks, function (Block $first, Block $second) {
                return $first->getOrder()->getValue() > $second->getOrder()->getValue();
            });
            $resultItem['items'] = array_map(function ($block) {
                $blockData = [
                    'type' => $type = $this->mapBlockKind($block->getKind()),
                ];

                if ($type === 'file') {
                    $blockData['data'] = $block->getFilePath() ? $block->getFilePath()->getPath() : '';
                } else if ($type === 'table') {
                    $blockData['data'] = $this->mapTable($block);
                }

                return $blockData;
            }, $blocks);

            if (!$paragraph->getChildren()->isEmpty()) {
                $resultItem['childs'] = $this->mapParagraphs($paragraph->getChildren());
            }

            $resultItems[] = $resultItem;
        }

        return $resultItems;
    }

    private function getParagraphHierarchyOrder(Paragraph $paragraph): string
    {
        $depth = $this->getDepth($paragraph);
        return $depth;
    }

    private function getDepth(Paragraph $paragraph): string
    {
        $order = (string)($paragraph->getOrder()->getValue() + 1);
        while($paragraph->getParent()){
            $paragraph = $paragraph->getParent();
            $order = (string)($paragraph->getOrder()->getValue() + 1) . '.' .$order;
        }
        return $order;
    }

    private function mapBlockKind(Kind $kind): string
    {
        switch ($kind->getValue()) {
            case Kind::TEXT:
                return 'file';
            case Kind::DICT:
                return 'table';
            default:
                return '';
        }
    }

    private function mapTable(Block $block): array
    {
        $rows = [];
        foreach ($this->dictionaries->fetch($block) as $dictionary) {
            $rows[] = [
                'columns' => [
                    [
                        'text' => $this->translator->translate(new Key($dictionary->key)),
                        'bold' => true,
                    ],
                    [
                        'text' => $dictionary->value,
                        'bold' => false,
                    ]
                ]
            ];
        }

        foreach ($block->getCustomValues() as $customValue) {
            $rows[] = [
                'columns' => [
                    [
                        'text' => $customValue->getKey(),
                        'bold' => true,
                    ],
                    [
                        'text' => $customValue->getValue(),
                        'bold' => false,
                    ],
                ],
            ];
        }
        return ['rows' => $rows];
    }
}
