<?php

namespace App\Models\responses;

class LoginResponse
{
    private int $status;
    private string $message;
    private string $token;

    /**
     * @param int|null $status
     * @param string|null $message
     * @param string|null $token
     */
    public function __construct(int $status = null, string $message = null, string $token = null)
    {
        $this->status = $status ?? 200;
        $this->message = $message ?? "Successfully logged in";
        $this->token = $token = "";
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token): void
    {
        $this->token = $token;
    }

    public function toArray()
    {
        return [
            "status" => $this->getStatus(),
            "message" => $this->getMessage(),
            "token" => $this->getToken()
        ];
    }

    public function invalidCredentialsErrorResponse()
    {
        return [
            "status" => 401,
            "message" => "Invalid Credentials",
            "token" => ""
        ];
    }
}
