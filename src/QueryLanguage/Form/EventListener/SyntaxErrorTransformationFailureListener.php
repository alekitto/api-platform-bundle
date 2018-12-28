<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\QueryLanguage\Form\EventListener;

use Fazland\ApiPlatformBundle\QueryLanguage\Exception\SyntaxError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Translation\TranslatorInterface;

class SyntaxErrorTransformationFailureListener implements EventSubscriberInterface
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
    public static function getSubscribedEvents(): array
    {
        return [
            FormEvents::POST_SUBMIT => ['convertTransformationFailureToFormError', 100]
        ];
    }

    /**
     * Converts an AQL syntax error into a form error, or process a transformation failure exception normally.
     *
     * @param FormEvent $event
     */
    public function convertTransformationFailureToFormError(FormEvent $event): void
    {
        $form = $event->getForm();
        $failure = $form->getTransformationFailure();

        if (null === $failure || !$form->isValid()) {
            return;
        }

        foreach ($form as $child) {
            if (!$child->isSynchronized()) {
                return;
            }
        }

        $clientDataAsString = is_scalar($form->getViewData()) ? (string)$form->getViewData() : \gettype($form->getViewData());
        $previous = $failure->getPrevious();

        do {
            if ($previous instanceof SyntaxError) {
                break;
            }
        } while (null !== $previous = $previous->getPrevious());

        if (null === $previous) {
            return;
        }

        $form->addError(new FormError($previous->getMessage(), $previous->getMessage(), array('{{ value }}' => $clientDataAsString), null, $failure));
    }
}
