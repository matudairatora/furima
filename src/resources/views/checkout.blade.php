<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    
    <title>Stripe Checkout - {{ $item->name }}</title>
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    {{-- Google Fontsを読み込むとかっこよくなります --}}
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP:wght@400;700&display=swap" rel="stylesheet">
    
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
</head>
<body>

    {{-- 全体を囲むラッパー --}}
    <div class="checkout-wrapper">
        
        {{-- 左側：商品情報エリア --}}
        <div class="product-section">
            <div class="product-content">
                <div class="header-logo">
                    {{-- サイトロゴなどがあればここに --}}
                    <span>COACHTECH</span>
                </div>
                <h1>{{ $item->name }}</h1>
                
                <div class="product-image-box">
                    {{-- 商品画像がある場合 --}}
                    <img src="{{ Storage::url($item->image) }}" alt="{{ $item->name }}">
                </div>
                
                <div class="price-tag">
                    <span class="currency">¥</span>
                    <span class="amount">{{ number_format($item->price) }}</span>
                </div>
                <p class="description">お支払いを完了して、購入手続きを終了してください。</p>
            </div>
        </div>

        {{-- 右側：決済フォームエリア --}}
        <div class="payment-section">
            <div class="form-container">
                <h1>決済情報の入力</h1>
                
                <form id="payment-form">
                    <input type="hidden" id="item-id" value="{{ $item->id }}"> 
                    
                    <div class="form-group">
                        <label for="email">メールアドレス</label>
                        <input type="email" id="email" placeholder="example@email.com" required>
                    </div>

                    <div class="form-group">
                        <label>カード情報</label>
                        <div id="payment-element">
                            </div>
                    </div>

                    <button id="submit" disabled>
                        <div class="spinner hidden" id="spinner"></div>
                        <span id="button-text">支払う (¥{{ number_format($item->price) }})</span>
                    </button>
                    <div id="payment-message" role="alert"></div>
                </form>
            </div>
        </div>

    </div>

    {{-- JavaScript部分はそのまま、または微調整 --}}
    <script>
        let stripe;
        let elements;
        const public_key = '{{ $stripePublicKey }}';

        document.addEventListener("DOMContentLoaded", function() {
            stripe = Stripe(public_key);
            initialize(); 
        });
        
        async function initialize() {
            const itemId = document.getElementById('item-id').value;
            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const clientSecretUrl = `/create-payment-intent/${itemId}`; 

            try {
                const response = await fetch(clientSecretUrl, {
                    method: "POST", 
                    headers: { 
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": csrfToken
                    },
                });
                
                if (!response.ok) {
                    throw new Error(`Payment Intentの作成に失敗しました（ステータス: ${response.status}）。`);
                }

                const { clientSecret } = await response.json();

                // Stripe Elementsの外観設定
                const appearance = { 
                    theme: 'stripe',
                    variables: {
                        colorPrimary: '#000000',
                    }
                };
                elements = stripe.elements({ appearance, clientSecret }); 
                
                const paymentElement = elements.create('payment');
                paymentElement.mount('#payment-element');
                
                document.getElementById('submit').disabled = false;

            } catch (error) {
                showMessage(`初期化エラー: ${error.message}`);
            }
        }

        document.querySelector("#payment-form").addEventListener("submit", async function(e) {
            e.preventDefault();
            
            if (!stripe || !elements) {
                showMessage('初期化が完了していません。');
                return;
            }

            const submitButton = document.getElementById('submit');
            submitButton.disabled = true;
            document.getElementById('button-text').textContent = '処理中...';
            showMessage(""); // メッセージクリア

            try {
                const email = document.getElementById('email').value;
                const itemId = document.getElementById('item-id').value;
                
                const { error } = await stripe.confirmPayment({
                    elements: elements,
                    confirmParams: {
                        payment_method_data: {
                            billing_details: { email: email },
                        },
                        return_url: "{{ route('stripe.complete') }}" + '?item_id=' + itemId, 
                    }
                });

                if (error) throw new Error(error.message);

            } catch (error) {
                showMessage(error.message);
                submitButton.disabled = false;
                document.getElementById('button-text').textContent = '支払う (¥{{ number_format($item->price) }})';
            }
        });

        function showMessage(messageText) {
            const msgContainer = document.getElementById('payment-message');
            msgContainer.textContent = messageText;
            // メッセージがあるときだけ表示
            msgContainer.style.display = messageText ? 'block' : 'none';
        }
    </script>
</body>
</html>