<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>주소 검색</title>
</head>

<body onload="init();">
    <form id="form" name="form" method="post">
        <input type="hidden" id="confmKey" name="confmKey" value="{{ config('services.juso.key') }}" />
        <input type="hidden" id="returnUrl" name="returnUrl" value="{{ url('/juso-popup') }}" />
        <input type="hidden" id="resultType" name="resultType" value="4" />
    </form>

    <script>
        function init() {
            var url = location.href;
            var inputYn = "{{ $inputYn }}";

            if (inputYn != "Y") {
                document.form.action = "https://business.juso.go.kr/addrlink/addrLinkUrl.do";
                document.form.submit();
            } else {
                console.log(
                    "{{ $addr['roadFullAddr'] ?? '' }}",
                    "{{ $addr['roadAddrPart1'] ?? '' }}",
                    "{{ $addr['addrDetail'] ?? '' }}",
                    "{{ $addr['roadAddrPart2'] ?? '' }}",
                    "{{ $addr['engAddr'] ?? '' }}",
                    "{{ $addr['jibunAddr'] ?? '' }}",
                    "{{ $addr['zipNo'] ?? '' }}",
                    "{{ $addr['admCd'] ?? '' }}",
                    "{{ $addr['rnMgtSn'] ?? '' }}",
                    "{{ $addr['bdMgtSn'] ?? '' }}",
                    "{{ $addr['detBdNmList'] ?? '' }}",
                    "{{ $addr['bdNm'] ?? '' }}",
                    "{{ $addr['bdKdcd'] ?? '' }}",
                    "{{ $addr['siNm'] ?? '' }}",
                    "{{ $addr['sggNm'] ?? '' }}",
                    "{{ $addr['emdNm'] ?? '' }}",
                    "{{ $addr['liNm'] ?? '' }}",
                    "{{ $addr['rn'] ?? '' }}",
                    "{{ $addr['udrtYn'] ?? '' }}",
                    "{{ $addr['buldMnnm'] ?? '' }}",
                    "{{ $addr['buldSlno'] ?? '' }}",
                    "{{ $addr['mtYn'] ?? '' }}",
                    "{{ $addr['lnbrMnnm'] ?? '' }}",
                    "{{ $addr['lnbrSlno'] ?? '' }}",
                    "{{ $addr['emdNo'] ?? '' }}"
                )
                opener.jusoCallBack(
                    "{{ $addr['roadFullAddr'] ?? '' }}",
                    "{{ $addr['roadAddrPart1'] ?? '' }}",
                    "{{ $addr['addrDetail'] ?? '' }}",
                    "{{ $addr['roadAddrPart2'] ?? '' }}",
                    "{{ $addr['engAddr'] ?? '' }}",
                    "{{ $addr['jibunAddr'] ?? '' }}",
                    "{{ $addr['zipNo'] ?? '' }}",
                    "{{ $addr['admCd'] ?? '' }}",
                    "{{ $addr['rnMgtSn'] ?? '' }}",
                    "{{ $addr['bdMgtSn'] ?? '' }}",
                    "{{ $addr['detBdNmList'] ?? '' }}",
                    "{{ $addr['bdNm'] ?? '' }}",
                    "{{ $addr['bdKdcd'] ?? '' }}",
                    "{{ $addr['siNm'] ?? '' }}",
                    "{{ $addr['sggNm'] ?? '' }}",
                    "{{ $addr['emdNm'] ?? '' }}",
                    "{{ $addr['liNm'] ?? '' }}",
                    "{{ $addr['rn'] ?? '' }}",
                    "{{ $addr['udrtYn'] ?? '' }}",
                    "{{ $addr['buldMnnm'] ?? '' }}",
                    "{{ $addr['buldSlno'] ?? '' }}",
                    "{{ $addr['mtYn'] ?? '' }}",
                    "{{ $addr['lnbrMnnm'] ?? '' }}",
                    "{{ $addr['lnbrSlno'] ?? '' }}",
                    "{{ $addr['emdNo'] ?? '' }}"
                );
                window.close();
            }
        }
    </script>
</body>

</html>
