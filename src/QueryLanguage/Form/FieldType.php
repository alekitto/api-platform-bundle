<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\QueryLanguage\Form;

use Fazland\ApiPlatformBundle\QueryLanguage\Form\EventListener\SyntaxErrorTransformationFailureListener;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\EventListener\TransformationFailureListener;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class FieldType extends AbstractType
{
    /**
     * @var null|TranslatorInterface
     */
    private $translator;

    public function __construct(?TranslatorInterface $translator = null)
    {
        $this->translator = $translator;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event): void {
                $form = $event->getForm();
                $dispatcher = $form->getConfig()->getEventDispatcher();
                $this->removeDefaultTransformationFailureListener($dispatcher);
            })
            ->addEventSubscriber(new SyntaxErrorTransformationFailureListener($this->translator))
            ->addViewTransformer(new DataTransformer\StringToExpresionTransformer())
        ;
    }

    private function removeDefaultTransformationFailureListener(EventDispatcherInterface $dispatcher): void
    {
        foreach ($dispatcher->getListeners(FormEvents::POST_SUBMIT) as $listener) {
            if (! \is_array($listener)) {
                continue;
            }

            $object = $listener[0];
            if (! $object instanceof TransformationFailureListener) {
                continue;
            }

            $dispatcher->removeListener(FormEvents::POST_SUBMIT, $listener);
        }
    }
}
