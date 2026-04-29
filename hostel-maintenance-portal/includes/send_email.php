<?php

/**
 * Email delivery is disabled system-wide.
 *
 * The portal now uses in-app notifications only.
 */
function sendEmail(string $toEmail, string $toName, string $subject, string $message): bool
{
    return false;
}