<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Нарушение бизнес-правила (время занято, вне графика, недопустимая смена статуса и т. п.).
 * Выводится в bootstrap/app.php как ответ 409 Conflict с полем message.
 */
class BusinessException extends RuntimeException {}
