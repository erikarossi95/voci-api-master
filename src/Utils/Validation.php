<?php
// src/Utils/Validation.php

namespace VociApi\Utils;

class Validation
{
    /**
     * Valida se un ID è un intero positivo.
     * @param mixed $id L'ID da validare.
     * @return bool True se l'ID è valido, false altrimenti.
     */
    public static function isValidId($id): bool
    {
        return filter_var($id, FILTER_VALIDATE_INT) !== false && $id > 0;
    }

    /**
     * Valida se una stringa non è vuota.
     * @param string|null $string La stringa da validare.
     * @return bool True se la stringa non è vuota, false altrimenti.
     */
    public static function isNotEmpty(?string $string): bool
    {
        return !empty($string);
    }

    /**
     * Valida se un array contiene solo interi positivi.
     * @param array $ids L'array di ID da validare.
     * @return bool True se tutti gli ID sono validi, false altrimenti.
     */
    public static function areValidIds(array $ids): bool
    {
        foreach ($ids as $id) {
            if (!self::isValidId($id)) {
                return false;
            }
        }
        return true;
    }
}
