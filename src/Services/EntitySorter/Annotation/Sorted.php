<?php
namespace App\Services\EntitySorter\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * @Annotation
 * @Target("CLASS")
 */
final class Sorted extends Annotation
{
    /** @Required */
    public string $property;

    public ?string $parent_property;

    public ?string $deleted_property = null;

    public ?string $deleted_value = null;

    public ?string $same_properties = null;
}
