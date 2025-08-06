<?php

namespace TransVoucher\Tests\Service;

use PHPUnit\Framework\TestCase;
use TransVoucher\Http\Client;
use TransVoucher\Service\PaymentService;
use TransVoucher\Model\Payment;
use TransVoucher\Model\PaymentList;
use TransVoucher\Exception\InvalidRequestException;
use TransVoucher\Exception\TransVoucherException;

class PaymentServiceTest extends TestCase
{
    private $mockClient;
    private $paymentService;

    protected function setUp(): void
    {
        $this->mockClient = $this->createMock(Client::class);
        $this->paymentService = new PaymentService($this->mockClient);
    }

    public function testCreatePaymentSuccess()
    {
        $requestData = [
            'amount' => 100.00,
            'currency' => 'USD',
            'title' => 'Test Payment',
            'description' => 'Test payment description',
            'customer_email' => 'test@example.com'
        ];

        $responseData = [
            'data' => [
                'id' => 1,
                'transaction_id' => 123,
                'payment_url' => 'https://transvoucher.site/pay/xyz',
                'amount' => 100.00,
                'currency' => 'USD',
                'status' => 'pending',
                'expires_at' => '2025-08-07T10:00:00Z'
            ]
        ];

        $this->mockClient->expects($this->once())
            ->method('post')
            ->with('/payment/create', $requestData)
            ->willReturn($responseData);

        $payment = $this->paymentService->create($requestData);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(1, $payment->id);
        $this->assertEquals(123, $payment->transaction_id);
        $this->assertEquals(100.00, $payment->amount);
        $this->assertEquals('USD', $payment->currency);
        $this->assertEquals('pending', $payment->status);
    }

    public function testCreatePaymentValidatesAmount()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Amount is required');

        $this->paymentService->create([]);
    }

    public function testCreatePaymentValidatesAmountMinimum()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Amount must be a number greater than or equal to 0.01');

        $this->paymentService->create(['amount' => 0]);
    }

    public function testCreatePaymentValidatesCurrency()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Currency must be one of: USD, EUR');

        $this->paymentService->create([
            'amount' => 100,
            'currency' => 'INVALID'
        ]);
    }

    public function testCreatePaymentValidatesEmail()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid email address');

        $this->paymentService->create([
            'amount' => 100,
            'customer_email' => 'invalid-email'
        ]);
    }

    public function testCreatePaymentValidatesRedirectUrl()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid redirect URL');

        $this->paymentService->create([
            'amount' => 100,
            'redirect_url' => 'invalid-url'
        ]);
    }

    public function testCreatePaymentValidatesLanguage()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Language must be one of: en, es, fr, de, it, pt, ru, zh, ja, ko');

        $this->paymentService->create([
            'amount' => 100,
            'lang' => 'invalid'
        ]);
    }

    public function testCreatePaymentValidatesTitle()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Title must not exceed 255 characters');

        $this->paymentService->create([
            'amount' => 100,
            'title' => str_repeat('a', 256)
        ]);
    }

    public function testCreatePaymentValidatesDescription()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Description must not exceed 1000 characters');

        $this->paymentService->create([
            'amount' => 100,
            'description' => str_repeat('a', 1001)
        ]);
    }

    public function testCreatePaymentValidatesExpirationDate()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Expiration date must be in the future');

        $this->paymentService->create([
            'amount' => 100,
            'expires_at' => '2020-01-01T00:00:00Z'
        ]);
    }

    public function testGetPaymentStatusSuccess()
    {
        $referenceId = 'ref_123';
        $responseData = [
            'data' => [
                'transaction_id' => 123,
                'reference_id' => $referenceId,
                'amount' => 100.00,
                'currency' => 'USD',
                'status' => 'completed',
                'paid_at' => '2025-08-06T10:00:00Z'
            ]
        ];

        $this->mockClient->expects($this->once())
            ->method('get')
            ->with("/payment/status/{$referenceId}")
            ->willReturn($responseData);

        $payment = $this->paymentService->status($referenceId);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(123, $payment->transaction_id);
        $this->assertEquals($referenceId, $payment->reference_id);
        $this->assertEquals('completed', $payment->status);
        $this->assertTrue($payment->isCompleted());
    }

    public function testGetPaymentStatusValidatesReferenceId()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Reference ID is required');

        $this->paymentService->status('');
    }

    public function testListPaymentsSuccess()
    {
        $params = [
            'limit' => 10,
            'status' => 'completed'
        ];

        $responseData = [
            'data' => [
                'payments' => [
                    [
                        'transaction_id' => 123,
                        'reference_id' => 'ref_123',
                        'amount' => 100.00,
                        'status' => 'completed'
                    ],
                    [
                        'transaction_id' => 124,
                        'reference_id' => 'ref_124',
                        'amount' => 200.00,
                        'status' => 'completed'
                    ]
                ],
                'has_more' => false,
                'next_page_token' => null,
                'count' => 2
            ]
        ];

        $this->mockClient->expects($this->once())
            ->method('get')
            ->with('/payment/list', $params)
            ->willReturn($responseData);

        $paymentList = $this->paymentService->list($params);

        $this->assertInstanceOf(PaymentList::class, $paymentList);
        $this->assertCount(2, $paymentList->getPayments());
        $this->assertFalse($paymentList->hasMore());
        $this->assertNull($paymentList->getNextPageToken());
        $this->assertEquals(2, $paymentList->getCount());
    }

    public function testListPaymentsValidatesLimit()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Limit must be an integer between 1 and 100');

        $this->paymentService->list(['limit' => 101]);
    }

    public function testListPaymentsValidatesStatus()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Status must be one of: pending, completed, failed, expired');

        $this->paymentService->list(['status' => 'invalid']);
    }

    public function testListPaymentsValidatesDateRange()
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('from_date must be before or equal to to_date');

        $this->paymentService->list([
            'from_date' => '2025-08-06',
            'to_date' => '2025-08-05'
        ]);
    }

    public function testHandlesInvalidApiResponse()
    {
        $this->mockClient->expects($this->once())
            ->method('post')
            ->willReturn(['error' => 'Some error']);

        $this->expectException(TransVoucherException::class);
        $this->expectExceptionMessage('Invalid response format from API');

        $this->paymentService->create(['amount' => 100]);
    }
}