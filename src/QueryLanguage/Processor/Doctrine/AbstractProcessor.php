<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\QueryLanguage\Processor\Doctrine;

use Fazland\ApiPlatformBundle\QueryLanguage\Expression\OrderExpression;
use Fazland\ApiPlatformBundle\QueryLanguage\Form\DTO\Query;
use Fazland\ApiPlatformBundle\QueryLanguage\Form\QueryType;
use Fazland\ApiPlatformBundle\QueryLanguage\Processor\ColumnInterface;
use Fazland\ApiPlatformBundle\QueryLanguage\Processor\Doctrine\PhpCr\Column;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractProcessor
{
    /**
     * @var ColumnInterface[]
     */
    protected array $columns;
    protected array $options;
    private FormFactoryInterface $formFactory;

    public function __construct(FormFactoryInterface $formFactory, array $options = [])
    {
        $this->options = $this->resolveOptions($options);
        $this->formFactory = $formFactory;
    }

    /**
     * Adds a column to this list processor.
     *
     * @param string                $name
     * @param array|ColumnInterface $options
     *
     * @return $this
     */
    public function addColumn(string $name, $options = []): self
    {
        if ($options instanceof ColumnInterface) {
            $this->columns[$name] = $options;

            return $this;
        }

        $resolver = new OptionsResolver();
        $options = $resolver
            ->setDefaults([
                'field_name' => $name,
                'walker' => null,
                'validation_walker' => null,
            ])
            ->setAllowedTypes('field_name', 'string')
            ->setAllowedTypes('walker', ['null', 'string', 'callable'])
            ->setAllowedTypes('validation_walker', ['null', 'string', 'callable'])
            ->resolve($options)
        ;

        $column = $this->createColumn($options['field_name']);

        if (null !== $options['walker']) {
            $column->customWalker = $options['walker'];
        }

        if (null !== $options['validation_walker']) {
            $column->validationWalker = $options['validation_walker'];
        }

        $this->columns[$name] = $column;

        return $this;
    }

    /**
     * Binds and validates the request to the internal Query object.
     *
     * @param Request $request
     *
     * @return Query|FormInterface
     */
    protected function handleRequest(Request $request)
    {
        $dto = new Query();
        $form = $this->formFactory->createNamed('', QueryType::class, $dto, [
            'limit_field' => $this->options['limit_field'],
            'skip_field' => $this->options['skip_field'],
            'order_field' => $this->options['order_field'],
            'continuation_token_field' => $this->options['continuation_token']['field'] ?? null,
            'columns' => $this->columns,
            'orderable_columns' => \array_keys(\array_filter($this->columns, static function (ColumnInterface $column): bool {
                return $column instanceof Column;
            })),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && ! $form->isValid()) {
            return $form;
        }

        return $dto;
    }

    /**
     * Creates a Column instance.
     *
     * @param string $fieldName
     *
     * @return ColumnInterface
     */
    abstract protected function createColumn(string $fieldName): ColumnInterface;

    /**
     * Gets the identifier field names from doctrine metadata.
     *
     * @return string[]
     */
    abstract protected function getIdentifierFieldNames(): array;

    /**
     * Parses the ordering expression for continuation token.
     *
     * @param OrderExpression $ordering
     *
     * @return array
     */
    protected function parseOrderings(OrderExpression $ordering): array
    {
        $checksumColumn = $this->getIdentifierFieldNames()[0];
        if (isset($this->options['continuation_token']['checksum_field'])) {
            $checksumColumn = $this->options['continuation_token']['checksum_field'];
            if (! $this->columns[$checksumColumn] instanceof Column) {
                throw new \InvalidArgumentException(\sprintf('%s is not a valid field for checksum', $this->options['continuation_token']['checksum_field']));
            }

            $checksumColumn = $this->columns[$checksumColumn]->fieldName;
        }

        $fieldName = $this->columns[$ordering->getField()]->fieldName;
        $direction = $ordering->getDirection();

        return [
            $fieldName => $direction,
            $checksumColumn => 'ASC',
        ];
    }

    /**
     * Resolves options for this processor.
     *
     * @param array $options
     *
     * @return array
     */
    private function resolveOptions(array $options): array
    {
        $resolver = new OptionsResolver();

        foreach (['order_field', 'skip_field', 'limit_field'] as $field) {
            $resolver
                ->setDefault($field, null)
                ->setAllowedTypes($field, ['null', 'string'])
            ;
        }

        $resolver
            ->setDefault('continuation_token', [
                'field' => 'continue',
                'checksum_field' => null,
            ])
            ->setAllowedTypes('continuation_token', ['bool', 'array'])
            ->setNormalizer('continuation_token', static function (Options $options, $value): array {
                if (true === $value) {
                    return [
                        'field' => 'continue',
                        'checksum_field' => null,
                    ];
                }

                if (! isset($value['field'])) {
                    throw new InvalidOptionsException('Continuation token field must be set');
                }

                return $value;
            })
        ;

        return $resolver->resolve($options);
    }
}
