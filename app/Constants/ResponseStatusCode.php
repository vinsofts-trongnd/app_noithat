<?php

namespace App\Constants;

/**
 * Class HttpResponse
 *
 * @package App\Constants
 */
class ResponseStatusCode
{
    const SWITCHING_PROTOCOLS = 101;
    const PROCESSING = 102;            // RFC2518
    const EARLY_HINTS = 103;           // RFC8297
    const OK = 200;
    const CREATED = 201;
    const ACCEPTED = 202;
    const NON_AUTHORITATIVE_INFORMATION = 203;
    const NO_CONTENT = 204;
    const RESET_CONTENT = 205;
    const PARTIAL_CONTENT = 206;
    const MULTI_STATUS = 207;          // RFC4918
    const ALREADY_REPORTED = 208;      // RFC5842
    const IM_USED = 226;               // RFC3229
    const MULTIPLE_CHOICES = 300;
    const MOVED_PERMANENTLY = 301;
    const FOUND = 302;
    const SEE_OTHER = 303;
    const NOT_MODIFIED = 304;
    const USE_PROXY = 305;
    const RESERVED = 306;
    const TEMPORARY_REDIRECT = 307;
    const PERMANENTLY_REDIRECT = 308;  // RFC7238
    const BAD_REQUEST = 400;
    const UNAUTHORIZED = 401;
    const PAYMENT_REQUIRED = 402;
    const FORBIDDEN = 403;
    const NOT_FOUND = 404;
    const METHOD_NOT_ALLOWED = 405;
    const NOT_ACCEPTABLE = 406;
    const PROXY_AUTHENTICATION_REQUIRED = 407;
    const REQUEST_TIMEOUT = 408;
    const CONFLICT = 409;
    const GONE = 410;
    const LENGTH_REQUIRED = 411;
    const PRECONDITION_FAILED = 412;
    const REQUEST_ENTITY_TOO_LARGE = 413;
    const REQUEST_URI_TOO_LONG = 414;
    const UNSUPPORTED_MEDIA_TYPE = 415;
    const REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    const EXPECTATION_FAILED = 417;
    const I_AM_A_TEAPOT = 418;                                               // RFC2324
    const MISDIRECTED_REQUEST = 421;                                         // RFC7540
    const UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    const LOCKED = 423;                                                      // RFC4918
    const FAILED_DEPENDENCY = 424;
    const PHONE_NOT_FOUND = 426;
    const CODE_PRESENT_NOT_EXISTS = 427;                                            // RFC4918
    const CODE_INTRO_ERROR  =430;
    const INTERNAL_SERVER_ERROR = 500;
    const CHECK_PHONE_SOCIAL = 428;
    const NOT_ENOUGH_POINT = 429;
    const VOUCHER_USED = 430;
    const NOT_ENOUGH_VOUCHER = 431;
    const NUMBER_VOUCHER_EXPIRED = 432;
    const NUMBER_CODE_EXPIRED = 433;
    const VOUCHER_EXPIRED_DATE = 434;
    const HAVE_CODE_INTRO = 435;
    const ALREADY_EXIST_PROFILE = 436;
    const PROFILE_DOES_NOT_EXIST =437;
    const ALREADY_EXIST_JOB = 438;
    const NO_RESULT = 439;
}
