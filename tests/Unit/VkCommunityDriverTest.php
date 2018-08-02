<?php

declare(strict_types=1);

namespace Tests\Unit;

use Tests\TestCase;
use GuzzleHttp\Client;
use FondBot\Helpers\Str;
use FondBot\Drivers\User;
use FondBot\Http\Request;
use GuzzleHttp\Psr7\Response;
use FondBot\Drivers\Vk\VkDriver;
use FondBot\Drivers\Vk\VkCommunityReceivedMessage;

/**
 * @property mixed|\Mockery\Mock|\Mockery\MockInterface guzzle
 * @property array                                      parameters
 * @property VkDriver                          vkCommunity
 */
class VkCommunityDriverTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->guzzle = $this->mock(Client::class);
        $this->vkCommunity = new VkDriver($this->guzzle);
        $this->vkCommunity->fill(
            $this->parameters = [
                'access_token' => Str::random(),
                'confirmation_token' => Str::random(),
            ],
            new Request([], [])
        );
    }

    /**
     * @expectedException \FondBot\Drivers\Exceptions\InvalidRequest
     * @expectedExceptionMessage Invalid type
     */
    public function test_verifyRequest_error_type()
    {
        $this->vkCommunity->fill($this->parameters, new Request(['type' => 'fake'], []));

        $this->vkCommunity->verifyRequest();
    }

    /**
     * @expectedException \FondBot\Drivers\Exceptions\InvalidRequest
     * @expectedExceptionMessage Invalid object
     */
    public function test_verifyRequest_empty_object()
    {
        $this->vkCommunity->fill($this->parameters, new Request(['type' => 'message_new'], []));

        $this->vkCommunity->verifyRequest();
    }

    /**
     * @expectedException \FondBot\Drivers\Exceptions\InvalidRequest
     * @expectedExceptionMessage Invalid user_id
     */
    public function test_verifyRequest_empty_object_user_id()
    {
        $this->vkCommunity->fill(
            $this->parameters,
            new Request(['type' => 'message_new', 'object' => ['body' => $this->faker()->word]], [])
        );

        $this->vkCommunity->verifyRequest();
    }

    /**
     * @expectedException \FondBot\Drivers\Exceptions\InvalidRequest
     * @expectedExceptionMessage Invalid body
     */
    public function test_verifyRequest_empty_object_body()
    {
        $this->vkCommunity->fill(
            $this->parameters,
            new Request(['type' => 'message_new', 'object' => ['user_id' => Str::random()]], [])
        );

        $this->vkCommunity->verifyRequest();
    }

    public function test_verifyRequest()
    {
        $this->vkCommunity->fill(
            $this->parameters,
            new Request([
                'type' => 'message_new',
                'object' => [
                    'user_id' => Str::random(),
                    'body' => $this->faker()->word,
                ],
            ], [])
        );

        $this->vkCommunity->verifyRequest();
    }

    public function test_getSender()
    {
        $userId = random_int(1, time());
        $senderId = $this->faker()->uuid;
        $senderFirstName = $this->faker()->firstName;
        $senderLastName = $this->faker()->lastName;

        $response = new Response(200, [], json_encode([
            'response' => [
                [
                    'id' => $senderId,
                    'first_name' => $senderFirstName,
                    'last_name' => $senderLastName,
                ],
            ],
        ]));

        $this->guzzle->shouldReceive('get')
            ->with(
                VkDriver::API_URL.'users.get',
                [
                    'query' => [
                        'user_ids' => $userId,
                        'v' => VkDriver::API_VERSION,
                    ],
                ]
            )
            ->once()
            ->andReturn($response);

        $this->vkCommunity->fill(
            $this->parameters,
            new Request([
                'object' => [
                    'user_id' => $userId,
                ],
            ], [])
        );

        $result = $this->vkCommunity->getUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($senderId, $result->getId());
        $this->assertEquals($senderFirstName.' '.$senderLastName, $result->getName());
        $this->assertNull($result->getUsername());

        // Sender already set
        $result = $this->vkCommunity->getUser();

        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($senderId, $result->getId());
        $this->assertEquals($senderFirstName.' '.$senderLastName, $result->getName());
        $this->assertNull($result->getUsername());
    }

    public function test_getMessage()
    {
        $this->vkCommunity->fill(
            $this->parameters,
            new Request([
                'type' => 'message_new',
                'object' => [
                    'body' => $text = $this->faker()->word,
                ],
            ], [])
        );

        $message = $this->vkCommunity->getMessage();
        $this->assertInstanceOf(VkCommunityReceivedMessage::class, $message);
        $this->assertSame($text, $message->getText());
        $this->assertFalse($message->hasAttachment());
        $this->assertNull($message->getLocation());
        $this->assertNull($message->getAttachment());
    }

    public function test_isVerificationRequest()
    {
        $this->vkCommunity->fill($this->parameters, new Request(['type' => 'confirmation'], []));

        $this->assertTrue($this->vkCommunity->isVerificationRequest());
    }

    public function test_verifyWebhook()
    {
        $this->assertEquals(
            $this->parameters['confirmation_token'],
            $this->vkCommunity->getParameter('confirmation_token')
        );

        $this->vkCommunity->verifyWebhook();
    }
}
