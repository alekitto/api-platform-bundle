<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\QueryLanguage\Walker\Validation;

use Fazland\ApiPlatformBundle\QueryLanguage\Expression\ExpressionInterface;
use Fazland\ApiPlatformBundle\QueryLanguage\Expression\Literal\LiteralExpression;

class EnumWalker extends ValidationWalker
{
    /**
     * @var array
     */
    private $values;

    public function __construct(array $values)
    {
        $this->values = $values;
    }

    /**
     * {@inheritdoc}
     */
    public function walkLiteral(LiteralExpression $expression)
    {
        if (\in_array($expression->getValue(), $this->values, true)) {
            return;
        }

        $this->addViolation('Value {{ value }} is not allowed. Must be one of {{ allowed_values }}.', [
            'value' => (string) $expression->getValue(),
            'allowed_values' => implode(', ', $this->values),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function walkOrder(string $field, string $direction)
    {
        $this->addViolation('Invalid operation');
    }

    /**
     * {@inheritdoc}
     */
    public function walkEntry(string $key, ExpressionInterface $expression)
    {
        $this->addViolation('Invalid operation');
    }
}
