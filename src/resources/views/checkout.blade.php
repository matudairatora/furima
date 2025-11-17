<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    
    <meta name="csrf-token" content="{{ csrf_token() }}"> 
    
    <title>Stripe Checkout - {{ $item->name }}</title>
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">
    <style>
        .product-info { border: 1px solid #ccc; padding: 15px; margin-bottom: 20px; }
        .product-info h2 { margin-top: 0; }
        #payment-message { margin-top: 10px; }
        #submit { padding: 10px 20px; font-size: 16px; cursor: pointer; }
    </style>
    
    <script type="text/javascript" src="https://js.stripe.com/v3/"></script>
    
</head>
<body>

    <h1>商品の購入</h1>

    <div class="product-info">
        <h2>{{ $item->name }}</h2>
        <p>価格: ¥{{ number_format($item->price) }}</p>
    </div>

    <form id="payment-form">
        <input type="hidden" id="item-id" value="{{ $item->id }}"> 
        
        <label for="email">Eメール</label>
        <input type="email" id="email" required>

        <h4>支払い情報（カード入力画面）</h4>
        <div id="payment-element">
            </div>

        <button id="submit" disabled>
            <span id="button-text">¥{{ number_format($item->price) }} を支払う</span>
        </button>
        <div id="payment-message" role="alert" style="color: red;"></div>
    </form>

 <script>
    let stripe;
    let elements;
    const public_key = '{{ $stripePublicKey }}';

    document.addEventListener("DOMContentLoaded", function() {
        // 1. Stripeオブジェクトの初期化
        stripe = Stripe(public_key);

        // ページロード時に初期化関数を呼び出す
        initialize(); 
    });
    
    /**
     * Payment Intentの clientSecret を取得し、Stripe Elementsを初期化する関数
     * ★この関数が、前のエラー (405 Method Not Allowed) を解消します。
     */
    async function initialize() {
        const itemId = document.getElementById('item-id').value;
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        
        // POSTルート（/create-payment-intent/{itemId}）に対応したURLを生成
        const clientSecretUrl = `/create-payment-intent/${itemId}`; 

        try {
            // サーバー側APIへPOSTリクエストを送信し、clientSecretを取得
            const response = await fetch(clientSecretUrl, {
                method: "POST", 
                headers: { 
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken // CSRFトークンの付与
                },
            });
            
            if (!response.ok) {
                // HTTPエラーの場合
                throw new Error(`Payment Intentの作成に失敗しました（ステータス: ${response.status}）。`);
            }

            const { clientSecret } = await response.json();

            // 2. clientSecret を使って Stripe Elements を初期化
            const appearance = { theme: 'stripe' };
            elements = stripe.elements({ appearance, clientSecret }); 
            
            // 3. Payment Elementを表示し、ボタンを有効化
            const paymentElement = elements.create('payment');
            paymentElement.mount('#payment-element');
            
            document.getElementById('submit').disabled = false;

        } catch (error) {
            showMessage(`初期化エラー: ${error.message}`);
        }
    }


    // フォーム送信時の処理 (お客様の既存のコード)
    document.querySelector("#payment-form").addEventListener("submit", async function(e) {
        e.preventDefault();
        
        if (!stripe || !elements) {
            showMessage('初期化が完了していません。ページを再読み込みしてください。');
            return;
        }

        const submitButton = document.getElementById('submit');
        submitButton.disabled = true;
        document.getElementById('button-text').textContent = '処理中...';
        showMessage("支払い処理を実行中...");

        try {
            const email = document.getElementById('email').value;
            const itemId = document.getElementById('item-id').value;
            
            // 支払い確定 (Stripeへデータを送信)
            const { error } = await stripe.confirmPayment({
                elements: elements,
                confirmParams: {
                    payment_method_data: {
                        billing_details: {
                            email: email,
                        },
                    },
                    // 支払い完了後のリダイレクト先 (web.phpの /stripe/complete ルートを参照)
                    return_url: "{{ route('stripe.complete') }}" + '?item_id=' + itemId, 
                }
            });

            if (error) {
                // エラー発生時はメッセージを表示し、フォームを再有効化
                throw new Error(error.message);
            }
            
            // 成功時は return_url へリダイレクトされるため、この後のコードは通常実行されない

        } catch (error) {
            showMessage(error.message || '支払い処理中に予期せぬエラーが発生しました。');
            submitButton.disabled = false;
            document.getElementById('button-text').textContent = '¥{{ number_format($item->price) }} を支払う';
        }
    });

    /**
     * ユーザーにメッセージを表示するヘルパー関数
     */
    function showMessage(messageText) {
        document.getElementById('payment-message').textContent = messageText;
    }
</script>
</body>
</html>