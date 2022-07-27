<?php
namespace App\Services\EntityLogger\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Versioned annotation for Loggable behavioral extension
 *
 * @Annotation
 * @Target("PROPERTY")
 */
final class Versioned extends Annotation
{
}
