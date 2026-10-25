<?php

class AnnouncementValidator
{
    public function isNonEmptyString($value): bool
    {
        return is_string($value) && trim($value) !== "";
    }
}
