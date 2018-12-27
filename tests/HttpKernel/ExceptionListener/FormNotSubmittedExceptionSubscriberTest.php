<?php declare(strict_types=1);

namespace Fazland\ApiPlatformBundle\Tests\ExceptionListener;

use Fazland\ApiPlatformBundle\HttpKernel\ExceptionListener\FormNotSubmittedExceptionSubscriber;
use Fazland\ApiPlatformBundle\PatchManager\Exception\FormNotSubmittedException;
use Fazland\ApiPlatformBundle\Tests\Fixtures\View\AppKernel;
use Prophecy\Argument;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelInterface;

class FormNotSubmittedExceptionSubscriberTest extends WebTestCase
{
    /**
     * @var FormNotSubmittedExceptionSubscriber
     */
    private $subscriber;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->subscriber = new FormNotSubmittedExceptionSubscriber();
    }

    public function testShouldSubscribeExceptionEvent(): void
    {
        self::assertArrayHasKey('kernel.exception', FormNotSubmittedExceptionSubscriber::getSubscribedEvents());
    }

    public function testShouldSkipIncorrectExceptions(): void
    {
        $event = $this->prophesize(GetResponseForExceptionEvent::class);
        $event->getException()->willReturn(new \Exception());
        $event->setResponse(Argument::any())->shouldNotBeCalled();

        $this->subscriber->onException($event->reveal());
    }

    public function testShouldHandleFormNotSubmittedException(): void
    {
        $event = $this->prophesize(GetResponseForExceptionEvent::class);
        $event->getException()->willReturn($exception = $this->prophesize(FormNotSubmittedException::class));
        $event->setResponse(Argument::type(Response::class))->shouldBeCalled();

        $exception->getForm()->willReturn($this->prophesize(FormInterface::class));

        $this->subscriber->onException($event->reveal());
    }

    /**
     * {@inheritdoc}
     */
    protected static function createKernel(array $options = []): KernelInterface
    {
        return new AppKernel('test', true);
    }

    public function testShouldInterceptFormNotSubmittedExceptionsAndReturnsCorrectResponse(): void
    {
        $client = static::createClient();
        $client->request('GET', '/form-not-submitted', [], [], ['HTTP_ACCEPT' => 'application/json']);

        $response = $client->getResponse();

        self::assertEquals(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        self::assertJsonStringEqualsJsonString('{"error":"No data sent.","name":"form"}', $response->getContent());
    }
}
