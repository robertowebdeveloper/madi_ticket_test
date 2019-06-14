<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TicketRestControllerTest extends WebTestCase
{
    private $test_ticket_id = 1;
    private $test_admin_id = 1;
    private $test_user_id = 2;

    function testSomething() {
        fwrite(STDERR, print_r('Per test ottimali sarebbe opportuno avere almeno un utente con ID 1 come ADMIN, un utente con ID 2 come "Utente" e un Ticket con ID 1, memorizzati nel database', TRUE));
        $this->assertEquals(1,1);
    }

    public function testGetListAction()
    {
        $this->client->request('GET' , '/api/' . $this->test_user_id . '/list');

        //Test su risposta
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        //Con utente inesistente
        $this->client->request('GET' , '/api/-1/list');
        //Utente inesistente
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        //Test su dati
        $this->assertEquals( '[]' , $this->client->getResponse()->getContent());
    }

    public function testGetTicketAction()
    {
        $this->client->request('GET' , '/api/' . $this->test_user_id . '/ticket/' . $this->test_ticket_id);

        //Test su risposta
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertTrue(
            $this->client->getResponse()->headers->contains(
                'Content-Type',
                'application/json'
            )
        );

        #dump( $this->client->getResponse()->getContent());#die;

        $this->assertContains(
            'date_opened_at',
            $this->client->getResponse()->getContent()
        );
    }

    public function testNewTicketAction()
    {
        $this->client->request('POST' , '/api/new', ['user_id' => $this->test_user_id , 'message' => 'Test new ticket']);

        //Test su risposta
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->assertContains(
            'ok',
            $this->client->getResponse()->getContent()
        );
    }

    public function testNewTicketMessageAction()
    {
        $this->client->request('POST' , '/api/new_message', ['user_id' => $this->test_user_id , 'ticket_id' => $this->test_ticket_id , 'message' => 'Test new ticket message']);

        //Test su risposta
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->assertContains(
            'ok',
            $this->client->getResponse()->getContent()
        );
    }

    public function testCloseTicketAction()
    {
        $this->client->request('POST' , '/api/close', ['user_id' => $this->test_user_id , 'ticket_id' => $this->test_ticket_id ]);

        //Test su risposta
        $this->assertEquals(201, $this->client->getResponse()->getStatusCode());

        $this->assertContains(
            'ok',
            $this->client->getResponse()->getContent()
        );
    }

    public function setUp()
    {
        $this->client = $this->createClient(['environment' => 'test']);
        $this->client->disableReboot();
        $this->em = $this->client->getContainer()->get('doctrine.orm.entity_manager');
        $this->em->beginTransaction();
    }

    public function tearDown()
    {
        $this->em->rollback();
    }
}