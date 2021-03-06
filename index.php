#!/bin/env php
<?php

// PLEASE NOTE: The multibyte characters in the internal and external
// DB host names are being counted by var_dump as 2 characters. So the
// string length in the output currently is 22 characters for "db­01­internal.local"
// instead of 20. Similarly for "db­01.company.tld", it shows 18 instead of 17.

// Main function call
var_dump(parseConfigFile(__DIR__ . "/config.txt"));

function parseConfigFile(string $configFilePath): array
{
    $configArray = [];
    $configFile = new \SplFileObject($configFilePath);

    foreach ($configFile as $line) {
        if (empty(trim($line))) {
            continue;
        }

        $configLineComponents = [];
        if (preg_match("/\s*([\w\.]+)\s*\=\s*(.+)$/", $line, $configLineComponents) !== 1) {
            continue;
        }

        if (sizeof($configLineComponents) < 3) {
            continue;
        }

        $configKey = $configLineComponents[1];
        $configValue = $configLineComponents[2];

        $configArray = array_merge_recursive($configArray, getConfigAsArray($configKey, $configValue));
    }

    return $configArray;
}

function getConfigAsArray(string $configKey, string $configValue): array
{
    $configKeySegments = explode('.', trim($configKey), 2);
    if (sizeof($configKeySegments) === 1) {
        return [
            $configKeySegments[0] => getTypedValueFromString($configValue)
        ];
    }

    $currentKey = $configKeySegments[0];
    return [
        $currentKey => getConfigAsArray($configKeySegments[1], $configValue)
    ];
}

function getTypedValueFromString(string $configValueString)
{
    $quoteLessConfigValue = trim($configValueString, "\"");

    if (strlen($quoteLessConfigValue) <= (strlen($configValueString) - 2)) {
        return $quoteLessConfigValue;
    }

    if ($quoteLessConfigValue === "true") {
        return true;
    }

    if ($quoteLessConfigValue === "false") {
        return false;
    }

    if (preg_match("/^\d+$/", $quoteLessConfigValue) === 1) {
        return (int) $quoteLessConfigValue;
    }

    throw new \Exception("Unrecognised config value type.");
}
