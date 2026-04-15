<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// Feature test mẫu để xác nhận route trang chủ phản hồi thành công.
class ExampleTest extends TestCase
{
    // Gọi HTTP GET tới trang chủ và mong nhận mã 200.
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }
}
