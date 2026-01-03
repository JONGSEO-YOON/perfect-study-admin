<?php

if (!function_exists('fix_mathtype_mfenced')) {
    /**
     * MathType에서 생성된 mfenced 태그를 호환 가능한 mrow 태그로 변환
     * 기존 mrow 태그 중복을 방지하고 올바른 구조로 변환
     * 
     * @param string $content TinyEditor 내용
     * @return string 변환된 내용
     */
    function fix_mathtype_mfenced(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        return preg_replace_callback(
            '/<mfenced([^>]*)>(.*?)<\/mfenced>/s',
            function ($matches) {
                $attributes = $matches[1];
                $innerContent = $matches[2];

                // 속성값 추출 (기본값 설정)
                $open = extract_mathml_attribute($attributes, 'open', '(');
                $close = extract_mathml_attribute($attributes, 'close', ')');

                // separators 속성이 명시적으로 있는지 확인
                $hasSeparators = preg_match('/separators\s*=\s*["\'][^"\']*["\']/', $attributes);
                $separator = $hasSeparators ? extract_mathml_attribute($attributes, 'separators', '') : '';

                // mfenced 전용 속성 제거하고 나머지 보존
                $otherAttrs = preg_replace(
                    '/\s*(open|close|separators)\s*=\s*["\'][^"\']*["\']/',
                    '',
                    $attributes
                );

                // 내부 콘텐츠에서 실제 수식 요소들을 추출
                $cleanElements = extract_math_elements($innerContent);

                if (empty($cleanElements)) {
                    return $matches[0]; // 변환할 요소가 없으면 원본 반환
                }

                $result = '<mrow' . $otherAttrs . '>';

                // 여는 괄호
                if (!empty($open)) {
                    $result .= '<mo>' . htmlspecialchars($open, ENT_XML1 | ENT_QUOTES) . '</mo>';
                }

                // 요소들과 구분자 (separators 속성이 명시적으로 있는 경우에만)
                foreach ($cleanElements as $index => $element) {
                    $result .= $element;

                    // 마지막 요소가 아니고 separators 속성이 명시적으로 있는 경우에만 구분자 추가
                    if ($index < count($cleanElements) - 1 && $hasSeparators && !empty($separator)) {
                        $sep = $separator[$index] ?? ($separator[0] ?? ',');
                        $result .= '<mo>' . htmlspecialchars($sep, ENT_XML1 | ENT_QUOTES) . '</mo>';
                    }
                }

                // 닫는 괄호
                if (!empty($close)) {
                    $result .= '<mo>' . htmlspecialchars($close, ENT_XML1 | ENT_QUOTES) . '</mo>';
                }

                $result .= '</mrow>';
                return $result;
            },
            $content
        );
    }
}

if (!function_exists('extract_mathml_attribute')) {
    /**
     * XML/HTML 속성에서 값을 추출하는 헬퍼 함수
     * 
     * @param string $attributes 속성 문자열
     * @param string $attributeName 찾을 속성명
     * @param string $default 기본값
     * @return string 추출된 값 또는 기본값
     */
    function extract_mathml_attribute(string $attributes, string $attributeName, string $default = ''): string
    {
        if (preg_match('/' . $attributeName . '\s*=\s*["\']([^"\']*)["\']/', $attributes, $matches)) {
            return $matches[1];
        }
        return $default;
    }
}

if (!function_exists('extract_math_elements')) {
    /**
     * MathML 내용에서 실제 수식 요소들을 추출 (중복 mrow 제거)
     * 
     * @param string $content MathML 내용
     * @return array 정리된 수식 요소들
     */
    function extract_math_elements(string $content): array
    {
        // 먼저 최상위 mrow 태그가 있는지 확인
        if (preg_match('/^<mrow[^>]*>(.*)<\/mrow>$/s', trim($content), $mrowMatch)) {
            // mrow 태그가 있다면 그 내용을 사용
            $content = $mrowMatch[1];
        }

        // 이제 개별 수식 요소들을 추출
        $elements = [];

        // 모든 MathML 태그를 찾되, mo 태그의 괄호는 제외
        if (preg_match_all('/<(mi|mn|mo|msup|msub|msubsup|mfrac|msqrt|mroot|munder|mover|munderover|mtable|mtext)[^>]*>.*?<\/\1>|<(mi|mn|mo|msup|msub|msubsup|mfrac|msqrt|mroot|munder|mover|munderover|mtable|mtext)[^>]*\/>/s', $content, $matches)) {
            foreach ($matches[0] as $element) {
                // mo 태그의 괄호 문자들은 제외 (여는 괄호, 닫는 괄호)
                if (preg_match('/<mo[^>]*>[\(\)\[\]\{\}]<\/mo>/', $element)) {
                    continue;
                }
                $elements[] = $element;
            }
        }

        return $elements;
    }
}

if (!function_exists('clean_mathml_content')) {
    /**
     * MathML 콘텐츠 전체를 정리하는 함수
     * mfenced 태그 변환 및 기타 호환성 문제 해결
     * 
     * @param string $content 원본 MathML 콘텐츠
     * @return string 정리된 MathML 콘텐츠
     */
    function clean_mathml_content(string $content): string
    {
        if (empty($content)) {
            return $content;
        }

        // mfenced 태그 변환
        $content = fix_mathtype_mfenced($content);

        // 추가적인 정리 작업들을 여기에 추가할 수 있습니다
        // 예: 불필요한 네임스페이스 제거, 속성 정리 등

        return $content;
    }
}
