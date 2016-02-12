<?php

#: Before
use mysli\markdown;
use mysli\markdown\parser;

# ------------------------------------------------------------------------------
#: Test Headers
$markdown = <<<MARKDOWN
# Preface

## Introduction

# Getting Started

## Introduction

### Synopsis

### Welcome to FreeBSD!

### About the FreeBSD Project

MARKDOWN;
$parser = new parser($markdown);
markdown::process($parser);
$header = $parser->get_processor('mysli.markdown.module.header');
return assert::equals($header->as_array(),
[
    'preface' => [
        'id' => 'preface',
        'fid' => 'preface',
        'title' => 'Preface',
        'level' => 1,
        'items' => [
            'introduction' => [
                'id' => 'introduction',
                'fid' => 'introduction',
                'title' => 'Introduction',
                'level' => 2,
                'items' => []
            ]
        ]
    ],
    'getting-started' => [
        'id' => 'getting-started',
        'fid' => 'getting-started',
        'title' => 'Getting Started',
        'level' => 1,
        'items' => [
            'introduction-2' => [
                'id' => 'introduction',
                'fid' => 'introduction-2',
                'title' => 'Introduction',
                'level' => 2,
                'items' => [
                    'synopsis' => [
                        'id' => 'synopsis',
                        'fid' => 'synopsis',
                        'title' => 'Synopsis',
                        'level' => 3,
                        'items' => []
                    ],
                    'welcome-to-freebsd' => [
                        'id' => 'welcome-to-freebsd',
                        'fid' => 'welcome-to-freebsd',
                        'title' => 'Welcome to FreeBSD!',
                        'level' => 3,
                        'items' => []
                    ],
                    'about-the-freebsd-project' => [
                        'id' => 'about-the-freebsd-project',
                        'fid' => 'about-the-freebsd-project',
                        'title' => 'About the FreeBSD Project',
                        'level' => 3,
                        'items' => []
                    ]
                ]
            ]
        ]
    ]
]);
