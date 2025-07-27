<?php

namespace App\Enums;

enum HttpStatus: int
{
    case STATUS_OK = 200;

    case REDIR_PERM = 301;
    case REDIR_TEMP = 302;

    case NOT_FOUND = 404;
}