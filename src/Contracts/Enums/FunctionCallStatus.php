<?php

namespace OpenFunctions\Core\Contracts\Enums;

enum FunctionCallStatus : string
{
    case PENDING = 'pending';
    case SUCCESS = 'success';
    case ERROR = 'error';
    case EXPIRED = 'expired';
    case REQUIRES_CONFIRMATION = 'requires_confirmation';
    case DECLINED = 'declined';
}