#!/bin/env php
<?php

// Main function call
var_dump(parse(__DIR__ . "/config.txt"));

function parse(string $configFilePath): array
{
    $configArray = [];
    $configFile = new \SplFileObject($configFilePath);

    foreach ($configFile as $line) {
        if (textIsEmpty($line)) {
            continue;
        }

        $config = explode("=", $line, 2);

        if (sizeof($config) < 2) {
            continue;
        }

        $configKeyComponents = preg_split("/\s+/", trim($config[0]));
        $configKey = end($configKeyComponents);

        $configValue = trim($config[1]);

        $configArray = array_merge_recursive($configArray, getConfigAsArray($configKey, $configValue));
    }

    return $configArray;
}

function textIsEmpty(string $text): bool
{
    return empty(trim($text));
}

function textBeginsWithHash(string $text): bool
{
    return $text[0] === "#";
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

    if ($quoteLessConfigValue === "true" ) {
        return true;
    }

    if ($quoteLessConfigValue === "false") {
        return false;
    }

    if (preg_match("/^\d+$/", $quoteLessConfigValue) === 1) {
        return (int) $quoteLessConfigValue;
    }
}
