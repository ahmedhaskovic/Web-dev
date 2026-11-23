<?php

// Every error that should reach the client as a specific HTTP status extends HttpException.
// The global error handler in index.php reads get_status() to choose the response code.

class HttpException extends Exception
{
    protected int $status = 500;

    public function get_status(): int
    {
        return $this->status;
    }
}

// 400: the request data breaks a validation or business rule.
class ValidationException extends HttpException
{
    protected int $status = 400;
}

// 401: no valid login. Used by the authentication middleware in Milestone 4.
class UnauthorizedException extends HttpException
{
    protected int $status = 401;
}

// 403: logged in, but the role is not allowed. Used by the authorization middleware in Milestone 4.
class ForbiddenException extends HttpException
{
    protected int $status = 403;
}

// 404: the requested record does not exist.
class NotFoundException extends HttpException
{
    protected int $status = 404;
}

// 503: a third-party API failed or timed out.
class ExternalServiceException extends HttpException
{
    protected int $status = 503;
}
