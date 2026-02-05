<?php

namespace Tests\Unit;

use App\Helpers\ApiResponse;
use Illuminate\Http\JsonResponse;
use Tests\TestCase;

class ApiResponseTest extends TestCase
{
    public function test_success_method_returns_correct_structure()
    {
        $response = ApiResponse::success(200, 'Success message', ['key' => 'value']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(200, $data['code']);
        $this->assertEquals('Success message', $data['message']);
        $this->assertEquals(['key' => 'value'], $data['data']);
    }

    public function test_error_method_returns_correct_structure()
    {
        $response = ApiResponse::error(400, 'Error message', ['error' => 'details']);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals(400, $data['code']);
        $this->assertEquals('Error message', $data['message']);
        $this->assertEquals(['error' => 'details'], $data['data']);
    }

    public function test_created_method_returns_201_status()
    {
        $response = ApiResponse::created('Resource created', ['id' => 1]);

        $this->assertEquals(201, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertTrue($data['success']);
        $this->assertEquals(201, $data['code']);
        $this->assertEquals('Resource created', $data['message']);
    }

    public function test_not_found_method_returns_404_status()
    {
        $response = ApiResponse::notFound('Resource not found');

        $this->assertEquals(404, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals(404, $data['code']);
        $this->assertEquals('Resource not found', $data['message']);
    }

    public function test_validation_error_method_returns_422_status()
    {
        $errors = ['email' => ['The email field is required.']];
        $response = ApiResponse::validationError('Validation failed', $errors);

        $this->assertEquals(422, $response->getStatusCode());

        $data = $response->getData(true);
        $this->assertFalse($data['success']);
        $this->assertEquals(422, $data['code']);
        $this->assertEquals('Validation failed', $data['message']);
        $this->assertEquals(['errors' => $errors], $data['data']);
    }

    public function test_with_meta_method_attaches_metadata()
    {
        $response = ApiResponse::withMeta(['page' => 1, 'total' => 100])
            ->success(200, 'Success with meta', ['items' => []]);

        $data = $response->getData(true);
        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals(['page' => 1, 'total' => 100], $data['meta']);
    }

    public function test_response_without_data_excludes_data_key()
    {
        $response = ApiResponse::success(200, 'Success without data');

        $data = $response->getData(true);
        $this->assertArrayNotHasKey('data', $data);
    }

    public function test_response_without_meta_excludes_meta_key()
    {
        $response = ApiResponse::success(200, 'Success without meta', ['key' => 'value']);

        $data = $response->getData(true);
        $this->assertArrayNotHasKey('meta', $data);
    }

    public function test_meta_is_reset_after_use()
    {
        // First response with meta
        ApiResponse::withMeta(['page' => 1])->success(200, 'First', []);

        // Second response without explicitly setting meta
        $response = ApiResponse::success(200, 'Second', []);

        $data = $response->getData(true);
        $this->assertArrayNotHasKey('meta', $data);
    }
}
