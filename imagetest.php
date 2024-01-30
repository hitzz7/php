<?php
use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Utils;

require_once 'vendor/autoload.php';

class imagetest extends TestCase
{
    private $baseUrl = 'http://localhost:8000/store.php';
    private $client;

    protected function setUp(): void
    {
        $this->client = new Client(['base_uri' => $this->baseUrl]);
    }

    public function testUploadImageForProduct()
    {
        // Replace with an existing product ID in your database
        $productId = 2;

        // Simulate a file upload (adjust the file path based on your test file)
        $imageFilePath = __DIR__ . '/test_image.jpg';
        $imageFile = Utils::streamFor(fopen($imageFilePath, 'r'));

        $response = $this->client->post("/image", [
            'multipart' => [
                [
                    'name' => 'product_id',
                    'contents' => $productId,
                ],
                [
                    'name' => 'image',
                    'contents' => $imageFile,
                    'filename' => 'test_image.jpg',
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Assert that the status code is 200 OK or 201 Created
        $this->assertContains($response->getStatusCode(), [200, 201]);

        // Add more assertions based on the expected structure of the response
        // For example, check if certain keys or values are present
        // $this->assertArrayHasKey('key', $data);
        // $this->assertEquals('expected_value', $data['key']);
    }
    public function testUpdateImageForProduct()
    {
        // Replace with an existing image ID and product ID in your database
        $imageId = 1;
        // $productId = 2;

        // Simulate a file upload for update (adjust the file path based on your test file)
        $updatedImageFilePath = __DIR__ . '/updated_test_image.jpg';
        $updatedImageFile = Utils::streamFor(fopen($updatedImageFilePath, 'r'));

        $response = $this->client->put("/update_image", [
            'multipart' => [
                [
                    'name' => 'image_id',
                    'contents' => $imageId,
                ],
                // [
                //     'name' => 'product_id',
                //     'contents' => $productId,
                // ],
                [
                    'name' => 'image',
                    'contents' => $updatedImageFile,
                    'filename' => 'updated_test_image.jpg',
                ],
            ],
        ]);

        $data = json_decode($response->getBody(), true);

        // Assert that the status code is 200 OK
        $this->assertEquals(200, $response->getStatusCode());

        // Add more assertions based on the expected structure of the response
        // For example, check if certain keys or values are present
        // $this->assertArrayHasKey('key', $data);
        // $this->assertEquals('expected_value', $data['key']);
    }

    public function testDeleteImage()
    {
        // Replace with an existing image ID in your database
        $imageId = 1;

        $response = $this->client->delete("/image/{$imageId}");

        // Assert that the status code is 200 OK
        $this->assertEquals(200, $response->getStatusCode());

        // Add more assertions based on the expected structure of the response
        // For example, check if certain keys or values are present
        // $this->assertArrayHasKey('key', $data);
        // $this->assertEquals('expected_value', $data['key']);
    }

    protected function tearDown(): void
    {
        $this->client = null;
    }
}
