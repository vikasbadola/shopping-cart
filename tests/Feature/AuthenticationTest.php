<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    /**
     * Test user registration endpoint.
     *
     */
    public function testRegister() {
        $response = $this->json('POST', '/api/auth/register', [
            'name'  => 'Test',
            'email'  =>  time().'test@example.com',
            'password'  => '123456789',
        ]);
        $response->assertStatus(200);
        // Receive our token
        $this->assertArrayHasKey('token',$response->json()['data']);
    }
    
    /**
     * Test user login endpoint.
     *
     */
    public function testLogin()
    {
        // Creating Users
        $response = $this->json('POST', '/api/auth/register', [
            'name'  => 'Test',
            'email'  => $email = time().'test@example.com',
            'password'  => $password = '123456789',
        ]);

        $response = $this->json('POST','/api/auth/login',[
            'email' => $email,
            'password' => $password,
        ]);
        // Determine whether the login is successful and receive token 
        $response->assertStatus(200);
    }
}
