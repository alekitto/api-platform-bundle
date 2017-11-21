<?php declare(strict_types=1);

namespace Kcs\ApiPlatformBundle\Annotation;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\ConfigurationInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Annotation
 */
class View implements ConfigurationInterface
{
    /**
     * @var int
     */
    public $statusCode = Response::HTTP_OK;

    /**
     * @var string[]
     */
    public $groups;

    /**
     * @var string
     */
    public $groupsProvider;

    /**
     * {@inheritdoc}
     */
    public function getAliasName()
    {
        return 'rest_view';
    }

    /**
     * {@inheritdoc}
     */
    public function allowArray()
    {
        return false;
    }
}
