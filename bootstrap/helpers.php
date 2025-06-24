<?php

// 1. 헬퍼 함수 생성 (app/Helpers/MathHelper.php 또는 bootstrap/helpers.php)
if (!function_exists('fix_mathtype_mfenced')) {
    /**
     * MathType에서 생성된 mfenced 태그를 호환 가능한 mrow 태그로 변환
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

                // 속성값 추출
                preg_match('/open\s*=\s*["\']([^"\']*)["\']/', $attributes, $openMatch);
                preg_match('/close\s*=\s*["\']([^"\']*)["\']/', $attributes, $closeMatch);
                preg_match('/separators\s*=\s*["\']([^"\']*)["\']/', $attributes, $sepMatch);

                $open = $openMatch[1] ?? '(';
                $close = $closeMatch[1] ?? ')';
                $separator = $sepMatch[1] ?? ',';

                // mfenced 전용 속성 제거하고 나머지 보존
                $otherAttrs = preg_replace('/\s*(open|close|separators)\s*=\s*["\'][^"\']*["\']/', '', $attributes);

                // MathML 요소들 추출
                preg_match_all('/<[^>]+>.*?<\/[^>]+>|<[^>]+\/>/', $innerContent, $elements);
                $elements = $elements[0];

                if (empty($elements)) {
                    return $matches[0]; // 변환할 요소가 없으면 원본 반환
                }

                $result = '<mrow' . $otherAttrs . '>';

                // 여는 괄호
                if (!empty($open)) {
                    $result .= '<mo>' . htmlspecialchars($open) . '</mo>';
                }

                // 요소들과 구분자
                foreach ($elements as $index => $element) {
                    $result .= $element;

                    // 마지막 요소가 아니면 구분자 추가
                    if ($index < count($elements) - 1) {
                        $sep = isset($separator[$index]) ? $separator[$index] : ($separator[0] ?? ',');
                        $result .= '<mo>' . htmlspecialchars($sep) . '</mo>';
                    }
                }

                // 닫는 괄호
                if (!empty($close)) {
                    $result .= '<mo>' . htmlspecialchars($close) . '</mo>';
                }

                $result .= '</mrow>';

                return $result;
            },
            $content
        );
    }
}
