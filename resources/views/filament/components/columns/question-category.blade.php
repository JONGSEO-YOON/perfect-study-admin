@php
if (!function_exists('convert_mfenced')) {
    function convert_mfenced(string $mathml): string
{
    return preg_replace_callback(
        '/<mfenced([^>]*)>(.*?)<\/mfenced>/s',
        function ($matches) {
            $attributes = $matches[1];
            $content = $matches[2];
            
            // 속성값 추출
            preg_match('/open\s*=\s*["\']([^"\']*)["\']/', $attributes, $openMatch);
            preg_match('/close\s*=\s*["\']([^"\']*)["\']/', $attributes, $closeMatch);
            preg_match('/separators\s*=\s*["\']([^"\']*)["\']/', $attributes, $sepMatch);
            
            $open = $openMatch[1] ?? '(';
            $close = $closeMatch[1] ?? ')';
            $separator = $sepMatch[1] ?? ',';
            
            // mfenced 전용 속성 제거하고 나머지 속성 보존
            $otherAttrs = preg_replace('/\s*(open|close|separators)\s*=\s*["\'][^"\']*["\']/', '', $attributes);
            
            // 내용을 태그 단위로 분할
            preg_match_all('/<[^>]+>.*?<\/[^>]+>|<[^>]+\/>/', $content, $elements);
            $elements = $elements[0];
            
            $result = '<mrow' . $otherAttrs . '>';
            $result .= '<mo>' . htmlspecialchars($open) . '</mo>';
            
            foreach ($elements as $index => $element) {
                $result .= $element;
                if ($index < count($elements) - 1) {
                    // 구분자 선택 (여러 구분자가 있으면 순서대로, 없으면 첫 번째 반복)
                    $sep = isset($separator[$index]) ? $separator[$index] : $separator[0];
                    $result .= '<mo>' . htmlspecialchars($sep) . '</mo>';
                }
            }
            
            $result .= '<mo>' . htmlspecialchars($close) . '</mo>';
            $result .= '</mrow>';
            
            return $result;
        },
        $mathml
    );
    }
}

@endphp

<div class="text-sm font-medium  flex flex-row items-center gap-x-3 pl-2">
    @if ($getRecord()->type === 'scope')
        <img src="/images/scope.svg" alt="범위" class="w-5 h-5">
    @else
        <img src="/images/question-type.svg" alt="범위" class="w-5 h-5">
    @endif
    {!! $getRecord()->name !!}
</div>
