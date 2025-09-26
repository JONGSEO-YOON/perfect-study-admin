<div class="min-h-screen">
    <!-- Main Content Area -->
    <main class="max-w-6xl mx-auto px-2 lg:px-6 py-2 sm:py-4">
        <!-- 상단 헤더 - X 버튼 -->
        <div class="bg-white p-0 rounded-lg mb-4 flex justify-between items-center">
            <h2 class="text-lg sm:text-xl font-bold text-stone-900">환불정책</h2>
            <button wire:click="closeModal" class="text-stone-400 hover:text-stone-600 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>

        <!-- 본문 내용 -->
        <div class="bg-white rounded-lg">
            <div class="mb-6">
                <h3 class="text-lg font-bold text-blue-600 mb-4">📋 교습비 등 반환</h3>

                <div class="space-y-4 text-sm text-stone-700 mb-6">
                    <p>• 학원설립·운영자는 학습자가 수강을 계속할 수 없는 경우 또는 학원의 등록말소 등으로 교습을 계속할 수 없는 경우 학습자로부터 받은 교습비 등을 반환 사유 발생일부터 5일 이내에 반환해야 합니다(「학원의 설립·운영 및 과외교습에 관한 법률」 제18조제1항 및 「학원의 설립·운영 및 과외교습에 관한 법률 시행령」 제18조제3항).</p>
                    <p>• 교습비 등 반환사유 및 반환기준은 다음과 같습니다(「학원의 설립·운영 및 과외교습에 관한 법률 시행령」 제18조제2항·제3항 및 별표 4).</p>
                </div>
            </div>

            <!-- 테이블 스크롤 컨테이너 -->
            <div class="overflow-x-auto">
                <table class="border-collapse border border-stone-300 text-xs text-left" style="width: 860px; table-layout: fixed;">
                    <thead>
                        <tr class="bg-stone-50">
                            <th class="border border-stone-300 px-2 py-1 text-left font-semibold whitespace-nowrap " colspan="3">구분</th>
                            <th class="border border-stone-300 px-2 py-1 text-left font-semibold whitespace-nowrap">반환사유 발생일</th>
                            <th class="border border-stone-300 px-2 py-1 text-left font-semibold whitespace-nowrap" colspan="2">반환금액</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2 text-left" colspan="3">교습자가 학원으로부터 격리된 경우</td>
                            <td class="border border-stone-300 px-2 py-2 text-left">학습자가 학원으로부터 격리된 날</td>
                            <td class="border border-stone-300 px-2 py-2" colspan="2">이미 납부한 교습비등-(이미 납부한 교습비등을 일할계산한 금액 × 교습 시작일 또는 학습장소 제공 시작일부터 학원으로부터 격리된 날의 전날까지의 일수)</td>
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2 text-left" colspan="3">교습자가 폐지된 경우 또는 교습의 정지명령을 받은 경우</td>
                            <td class="border border-stone-300 px-2 py-2 text-left" rowspan="2">학원설립·운영자, 교습자 또는 개인과외교습자가 교습을 할 수 없거나 학습장소를 제공할 수 없게 된 날</td>
                            <td class="border border-stone-300 px-2 py-2" rowspan="2" colspan="2">이미 납부한 교습비등-(이미 납부한 교습비등을 일할계산한 금액 × 교습 시작일 또는 학습장소 제공 시작일부터 교습을 할 수 없거나 학습장소를 제공할 수 없게 된 날의 전날까지의 일수)</td>
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2 text-left" colspan="3">교습자가 교습을 할 수 없거나 학습장소를 제공할 수 없게 된 경우</td>
                            {{-- <td class="border border-stone-300 px-2 py-2 text-left"></td> --}}
                            {{-- <td class="border border-stone-300 px-2 py-2"></td> --}}
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2" rowspan="8">학습자가 본인의 의사로 수강 또는 학습 장소 사용 을 포기한 경우</td>
                            <td class="border border-stone-300 px-2 py-2" rowspan="6">교습기간 또는 학습장소 사용기간이 1개월 이내인 겨우</td>
                            <td class="border border-stone-300 px-2 py-2" rowspan="4">독서실을 제외한 학원, 교습소 및 개인과외 교습자의 경우</td>
                            <td class="border border-stone-300 px-2 py-2" rowspan="4">학습자가 본인의 의사로 수강을 포기한 날</td>
                            <td class="border border-stone-300 px-2 py-2">교습 시작 전</td>
                            <td class="border border-stone-300 px-2 py-2">이미 납부한 교습지등의 전액</td>
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2">교습 시작 후부터 총 교습시간의 1/3 경과전까지</td>
                            <td class="border border-stone-300 px-2 py-2">이미 납부한 교습비등의 2/3에 해당하는 금액</td>

                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2">총 교습시간의 1/3 경과 후부터 1/2 경 과 전까지</td>
                            <td class="border border-stone-300 px-2 py-2">이미 납부한 교습비등의 1/2에 해 당하는 금액</td>
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2">총 교습시간의 1/2 경과 후</td>
                            <td class="border border-stone-300 px-2 py-2">없음</td>
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2" rowspan="2">독서실의 경우</td>
                            <td class="border border-stone-300 px-2 py-2" rowspan="2">학습자가 본인의 의사로 학습장소 사용을 포기한 날</td>
                            <td class="border border-stone-300 px-2 py-2">학습장소 사용 전</td>
                            <td class="border border-stone-300 px-2 py-2">이미 납부한 교습비등의 전액</td>

                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2">학습장소 사용 후</td>
                            <td class="border border-stone-300 px-2 py-2">이미 납부한 교습비등-(법 제15조 제3항 전단에 따라 게시된 1일 교 습비등 x 학습장소 사용 시작일부 터 학습장소 사용을 포기한 날의 전날까지의 일수)</td>
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2" rowspan="2" colspan="2">교습기간 또는 학습장소 사용기간이 1개월을 초과하는 경우</td>
                            <td class="border border-stone-300 px-2 py-2" rowspan="2">학습자가 본인의 의사로 수강 또는 학습장소 사용을 포기한 날</td>
                            <td class="border border-stone-300 px-2 py-2">교습 시작 전 또는 학습장소 사용 전</td>
                            <td class="border border-stone-300 px-2 py-2">이미 납부한 교습비 등의 전액</td>
                        </tr>
                        <tr>
                            <td class="border border-stone-300 px-2 py-2">교습 시작 후 또는 학습장소 사용 후</td>
                            <td class="border border-stone-300 px-2 py-2">반환사유가 발생한 해당 월의 반환 대상 교습비등(교습기간 또는 학습 장소 사용기간이 1개월 이내인 경 우의 기준에 따라 산출한 금액을 말한다)에 나머지 월의 교습비 등 의 전액을 합산한 금액</td>

                        </tr>
                 
                 
                       
                    </tbody>
                </table>
            </div>

            <div class="mt-6 space-y-2 text-xs text-stone-600">
                <p>※ 총 교습시간은 교습기간 중의 총 교습시간을 말하며, 반환금액의 산정은 반환사유가 발생한 날까지 경과된 교습시간을 기준으로 합니다.</p>
                <p>※ 원격교습의 경우 반환금액은 교습내용을 실제 수강한 부분(인터넷으로 수강하거나 학습기기로 저장한 것을 말함)에 해당하는 금액을 뺀 금액으로 합니다.</p>
            </div>
        </div>
    </main>
</div>