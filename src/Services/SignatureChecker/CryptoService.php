<?php

namespace App\Services\SignatureChecker;

use App\Services\SignatureChecker\CryptoLib;

class CryptoService
{
    private CryptoLib $cryptoLib;

    public function __construct(CryptoLib $cryptoLib)
    {
        $this->cryptoLib = $cryptoLib;
    }

    public function verifySignature(string $contentPath, string $sigPath)
    {
        $this->ensurePemFormat($sigPath);

        if (!$this->checkSig4file($sigPath, $contentPath)) {
            throw new \DomainException('Подпись не подходит к файлу');
        }

        $info = $this->getSigInfo($sigPath, $contentPath);

        if (isset($info['error']) || !isset($info['signers'])) {
           throw new \DomainException('Ошибки при распознавании. Подпись не подходит к файлу');
        }

        return $info['signers'];
    }

    private function ensurePemFormat(string $sig_path)
    {
        $inform = CryptoLib::openssl_cms_input_format(['in' => $sig_path]);
        $inform = $inform['result'] ?? null;

        if ($inform === null) {
            throw new \DomainException('Не удалось распознать подпись');
        }

        if ($inform === 'PEM') {
            return;
        }

        if ($inform === 'SMIME') {
            @unlink($sig_path);
            throw new \DomainException('Подпись в формате SMIME не поддерживается');
        }

        if ($inform !== 'DER') {
            throw new \DomainException("Wrong inform $inform");
        }

        exec("openssl cms -cmsout -in '$sig_path' -out '$sig_path' -inform der -outform pem",$output, $result);

        if ($result !== 0) {
            @unlink($sig_path);
            throw new \DomainException('Ошибка конвертации DER в PEM');
        }
    }

    private function checkSig4file($sig_path, $file_path)
    {
        if (!is_file($sig_path)) {
            throw new \InvalidArgumentException('No sig');
        }

        if (!is_file($file_path)) {
            throw new \InvalidArgumentException('No pdf');
        }

        $form = CryptoLib::openssl_cms_input_format(
            [
                'in' => $sig_path,
            ]
        );
        $result = CryptoLib::openssl_cms_verify_content(
            [
                'inform' => $form['result'],
                'in' => $sig_path,
                'content' => $file_path
            ]
        );

        return $result['status'] === CryptoLib::SUCCESS;
    }

    /**
     * @param $sig_path
     * @param $content_path
     *
     * @return mixed
     * @throws \Exception
     */
    private function getSigInfo(string $sig_path, string $content_path)
    {
        $signers = @tempnam("/tmp/$sig_path", 'sig');
        if ($signers === false) {
            throw new \DomainException('Cant create tempnam');
        }

        $inform = CryptoLib::openssl_cms_input_format(['in' => $sig_path]);
        $inform = $inform['result'];

        $sig_path_arg = escapeshellarg($sig_path);
        $pdf_path_arg = escapeshellarg($content_path);
        $signers_arg = escapeshellarg($signers);
        $inform_arg = escapeshellarg($inform);
        exec(
            "openssl cms \
            -verify \
            -binary \
            -in $sig_path_arg \
            -out /dev/null \
            -signer $signers_arg \
            -noverify \
            -no_attr_verify \
            -no_content_verify \
            -inform $inform_arg \
            -content $pdf_path_arg
        ", $out, $code
        );

        if ($code !== 0) {
            throw new \DomainException('Whoops ' . json_encode($out));
        }


        $pattern = '/^' . CryptoLib::CRT_HEADER . '$.*?^' . CryptoLib::CRT_FOOTER . '$/ms';
        preg_match_all($pattern, @file_get_contents($signers), $signerCerts) || $signerCerts = [[]];
        @unlink($signers);

        $signers = [];

        foreach ($signerCerts[0] as $cert) {
            $signerFile = @tempnam('/tmp/' . random_int(0, 10000), 'signer');
            file_put_contents($signerFile, $cert . PHP_EOL);

            exec(
                "openssl x509 \
                    -noout \
                    -in '$signerFile' \
                    -inform pem \
                    -subject \
                    -nameopt 'lname,sep_multiline,utf8'
                ", $out, $code
            );

            @unlink($signerFile);

            if ($code !== 0) {
                return ['error' => $out];
            }

            $signerData = [];
            foreach ($out as $line) {
                $line = trim($line, " \t");
                list($key, $value) = explode('=', $line);
                $signerData[$key] = $value;
            }
            $signers[] = $signerData;
        }

        $sigInfo['signers'] = $signers;

        return $sigInfo;
    }
}
