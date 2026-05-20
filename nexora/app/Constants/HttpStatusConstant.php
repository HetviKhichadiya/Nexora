<?php

namespace App\Constants;

class HttpStatusConstant
{
    // ==================== SUCCESS CODES (2xx) ====================

    /** Request succeeded */
    const OK = 200;

    /** Resource created successfully */
    const CREATED = 201;

    /** Request accepted for processing */
    const ACCEPTED = 202;

    /** No content to return */
    const NO_CONTENT = 204;

    // ==================== CLIENT ERROR CODES (4xx) ====================

    /** Bad request - invalid syntax */
    const BAD_REQUEST = 400;

    /** Authentication required */
    const UNAUTHORIZED = 401;

    /** Payment required */
    const PAYMENT_REQUIRED = 402;

    /** Client does not have access rights */
    const FORBIDDEN = 403;

    /** Resource not found */
    const NOT_FOUND = 404;

    /** Method not allowed */
    const METHOD_NOT_ALLOWED = 405;

    /** Conflict with current state */
    const CONFLICT = 409;

    /** Resource no longer available */
    const GONE = 410;

    /** Validation failed */
    const UNPROCESSABLE_ENTITY = 422;

    /** Too many requests */
    const TOO_MANY_REQUESTS = 429;

    // ==================== SERVER ERROR CODES (5xx) ====================

    /** Internal server error */
    const INTERNAL_SERVER_ERROR = 500;

    /** Functionality not implemented */
    const NOT_IMPLEMENTED = 501;

    /** Bad gateway */
    const BAD_GATEWAY = 502;

    /** Service unavailable */
    const SERVICE_UNAVAILABLE = 503;

    /** Gateway timeout */
    const GATEWAY_TIMEOUT = 504;
}