#!/bin/env php
<?php

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

        $lineComponents = [];
        if (preg_match("/\s*([\w\.]+)\s*\=\s*(.+)$/", $line, $lineComponents) !== 1) {
            continue;
        }

        if (sizeof($lineComponents) < 3) {
            continue;
        }

        $configKey = $lineComponents[1];
        $configValue = $lineComponents[2];

        $configArray = array_merge_recursive($configArray, getConfigAsArray($configKey, $configValue));
    }

    return $configArray;
}

function getConfigAsArray(string $configKey, string $configValue): array
{
    $configKeys = explode('.', trim($configKey), 2);
    if (sizeof($configKeys) === 1) {
        return [
            $configKeys[0] => getTypedValueFromString($configValue)
        ];
    }

    $currentKey = $configKeys[0];
    return [
        $currentKey => getConfigAsArray($configKeys[1], $configValue)
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
}
