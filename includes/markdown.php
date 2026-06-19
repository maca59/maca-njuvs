<?php
/**
 * Simple markdown rendering for admin guide documents.
 *
 * @package Maca_Njuvs
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Allowed HTML tags for rendered markdown.
 *
 * @return array<string, array<string, bool>>
 */
function maca_menulist_get_markdown_allowed_html() {
    return array(
        'h1'     => array(),
        'h2'     => array(
            'id' => true,
        ),
        'h3'     => array(
            'id' => true,
        ),
        'h4'     => array(),
        'p'      => array(),
        'strong' => array(),
        'em'     => array(),
        'a'      => array(
            'href'   => true,
            'target' => true,
            'rel'    => true,
        ),
        'pre'    => array(),
        'code'   => array(),
        'ul'     => array(),
        'ol'     => array(),
        'li'     => array(),
        'table'  => array(),
        'thead'  => array(),
        'tbody'  => array(),
        'tr'     => array(),
        'th'     => array(),
        'td'     => array(),
    );
}

/**
 * Format inline markdown fragments.
 *
 * @param string $text Raw text.
 * @return string
 */
function maca_menulist_format_inline_markdown($text) {
    $text = (string) $text;
    if ($text === '') {
        return '';
    }

    $links = array();
    $text = preg_replace_callback(
        '/\[(.*?)\]\((https?:\/\/[^\s)]+)\)/',
        function ($matches) use (&$links) {
            $placeholder = '%%MACA_LINK_' . count($links) . '%%';
            $links[$placeholder] = '<a href="' . esc_url($matches[2]) . '" target="_blank" rel="noopener noreferrer">' . esc_html($matches[1]) . '</a>';
            return $placeholder;
        },
        $text
    );

    $text = esc_html($text);
    $text = preg_replace('/\*\*(.*?)\*\*/', '<strong>$1</strong>', $text);
    $text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);

    if (!empty($links)) {
        $text = str_replace(array_keys($links), array_values($links), $text);
    }

    return $text;
}

/**
 * Convert markdown to HTML.
 *
 * @param string $markdown Markdown source.
 * @return string
 */
function maca_menulist_markdown_to_html($markdown) {
    $html = str_replace(array("\r\n", "\r"), "\n", (string) $markdown);
    $lines = explode("\n", $html);
    $output = array();
    $in_ul = false;
    $in_ol = false;
    $in_table = false;
    $table_header_pending = false;
    $in_code = false;
    $code_lines = array();

    foreach ($lines as $line) {
        $trimmed = trim($line);

        if ($in_code) {
            if ($trimmed === '```') {
                $output[] = '<pre><code>' . esc_html(implode("\n", $code_lines)) . '</code></pre>';
                $code_lines = array();
                $in_code = false;
            } else {
                $code_lines[] = $line;
            }
            continue;
        }

        if ($trimmed === '```') {
            if ($in_ul) {
                $output[] = '</ul>';
                $in_ul = false;
            }
            if ($in_ol) {
                $output[] = '</ol>';
                $in_ol = false;
            }
            if ($in_table) {
                $output[] = '</tbody></table>';
                $in_table = false;
            }
            $in_code = true;
            continue;
        }

        if ($trimmed === '') {
            if ($in_ul) {
                $output[] = '</ul>';
                $in_ul = false;
            }
            if ($in_ol) {
                $output[] = '</ol>';
                $in_ol = false;
            }
            if ($in_table) {
                $output[] = '</tbody></table>';
                $in_table = false;
            }
            continue;
        }

        if (preg_match('/^\|(.+)\|$/', $trimmed)) {
            if (preg_match('/^\|[\s\-:|]+\|$/', $trimmed)) {
                continue;
            }

            $cells = array_map('trim', explode('|', trim($trimmed, '|')));
            if (!$in_table) {
                $output[] = '<table><tbody>';
                $in_table = true;
                $table_header_pending = true;
            }

            $tag = $table_header_pending ? 'th' : 'td';
            $table_header_pending = false;
            $row = '<tr>';
            foreach ($cells as $cell) {
                $row .= '<' . $tag . '>' . maca_menulist_format_inline_markdown($cell) . '</' . $tag . '>';
            }
            $row .= '</tr>';
            $output[] = $row;
            continue;
        }

        if ($in_table) {
            $output[] = '</tbody></table>';
            $in_table = false;
        }

        if (preg_match('/^\d+\.\s+(.*)$/', $trimmed, $matches)) {
            if ($in_ul) {
                $output[] = '</ul>';
                $in_ul = false;
            }
            if (!$in_ol) {
                $output[] = '<ol>';
                $in_ol = true;
            }
            $output[] = '<li>' . maca_menulist_format_inline_markdown($matches[1]) . '</li>';
            continue;
        }

        if (preg_match('/^-\s+(.*)$/', $trimmed, $matches)) {
            if ($in_ol) {
                $output[] = '</ol>';
                $in_ol = false;
            }
            if (!$in_ul) {
                $output[] = '<ul>';
                $in_ul = true;
            }
            $output[] = '<li>' . maca_menulist_format_inline_markdown($matches[1]) . '</li>';
            continue;
        }

        if ($in_ul) {
            $output[] = '</ul>';
            $in_ul = false;
        }
        if ($in_ol) {
            $output[] = '</ol>';
            $in_ol = false;
        }

        if (preg_match('/^#### (.*)$/', $trimmed, $matches)) {
            $output[] = '<h4>' . maca_menulist_format_inline_markdown($matches[1]) . '</h4>';
        } elseif (preg_match('/^### \{#([a-z0-9_-]+)\}\s+(.*)$/', $trimmed, $matches)) {
            $output[] = '<h3 id="' . esc_attr($matches[1]) . '">' . maca_menulist_format_inline_markdown($matches[2]) . '</h3>';
        } elseif (preg_match('/^### (.*)$/', $trimmed, $matches)) {
            $output[] = '<h3>' . maca_menulist_format_inline_markdown($matches[1]) . '</h3>';
        } elseif (preg_match('/^## \{#([a-z0-9_-]+)\}\s+(.*)$/', $trimmed, $matches)) {
            $output[] = '<h2 id="' . esc_attr($matches[1]) . '">' . maca_menulist_format_inline_markdown($matches[2]) . '</h2>';
        } elseif (preg_match('/^## (.*)$/', $trimmed, $matches)) {
            $output[] = '<h2>' . maca_menulist_format_inline_markdown($matches[1]) . '</h2>';
        } elseif (preg_match('/^# (.*)$/', $trimmed, $matches)) {
            $output[] = '<h1>' . maca_menulist_format_inline_markdown($matches[1]) . '</h1>';
        } else {
            $output[] = '<p>' . maca_menulist_format_inline_markdown($trimmed) . '</p>';
        }
    }

    if ($in_ul) {
        $output[] = '</ul>';
    }
    if ($in_ol) {
        $output[] = '</ol>';
    }
    if ($in_table) {
        $output[] = '</tbody></table>';
    }
    if ($in_code && !empty($code_lines)) {
        $output[] = '<pre><code>' . esc_html(implode("\n", $code_lines)) . '</code></pre>';
    }

    return implode("\n", $output);
}

/**
 * Render a markdown file from disk.
 *
 * @param string $path Absolute file path.
 * @return string Safe HTML or empty string.
 */
function maca_menulist_render_markdown_file($path) {
    if (!is_string($path) || $path === '' || !file_exists($path)) {
        return '';
    }

    $content = file_get_contents($path);
    if (!is_string($content) || $content === '') {
        return '';
    }

    return wp_kses(maca_menulist_markdown_to_html($content), maca_menulist_get_markdown_allowed_html());
}
