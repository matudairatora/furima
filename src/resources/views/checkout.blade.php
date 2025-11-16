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
    // ----------------------------------------------------
    // グローバル変数として定義
    // ----------------------------------------------------
    let stripe;
    let elements;
    // メタタグからCSRFトークンを取得
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // ページロード時に初期化を実行
    initialize();

    async function initialize() {
        // ボタンを無効化し、メッセージを表示
        document.getElementById('submit').disabled = true;
        showMessage("決済情報を準備中...");

        try {
            const itemId = document.getElementById('item-id').value;
            
            // 1. Stripeクライアントの初期化
            stripe = Stripe('{{ $stripePublicKey }}'); 

            // Laravelのルート定義に基づいて、正しいPOSTリクエストURLを生成
            const sessionCreationUrl = '{{ route('create-payment-intent', ['itemId' => ':itemId']) }}'.replace(':itemId', itemId);

            // 2. サーバーサイドから client_secret を取得
            const response = await fetch(sessionCreationUrl, {
                method: "POST",
                headers: { 
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": csrfToken, // CSRFトークンを送信
                },
            });
            
            let data = {};
            
            if (!response.ok) {
                try {
                    data = await response.json();
                    throw new Error(data.error || `リクエストが失敗しました（ステータス: ${response.status}）。`);
                } catch (e) {
                    throw new Error(`リクエストが失敗しました（ステータス: ${response.status}）。`);
                }
            }
            
            data = await response.json(); 
            if (!data.clientSecret) {
                throw new Error('サーバーから決済情報（clientSecret）が取得できませんでした。');
            }
            
            // ★★★ 修正箇所: decodeURIComponent()を削除する ★★★
            // JSONレスポンスの文字列（エスケープされていてもJavaScriptのJSONパーサーが自動処理済み）を
            // そのまま使用します。
            const clientSecret = data.clientSecret; // 修正後の正しいコード
            
            // ----------------------------------------------------
            // elements() APIを使用し、Payment Elementをマウント
            // ----------------------------------------------------
            const appearance = {
                theme: 'stripe',
            };
            
            elements = stripe.elements({ appearance, clientSecret });
            
            const paymentElement = elements.create("payment");
            paymentElement.mount("#payment-element");

            showMessage("カード情報を入力してください。"); 
            document.getElementById('submit').disabled = false;

        } catch (error) {
            console.error("Error during initialization:", error);
            // ユーザーに具体的なエラー内容を伝える
            showMessage(error.message || '決済準備中に予期せぬエラーが発生しました。');
            document.getElementById('submit').disabled = true;
        }
    }

    document.getElementById('payment-form').addEventListener('submit', async function(event) {
        event.preventDefault();
        
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
                    return_url: '{{ config('app.url') }}/stripe/complete?item_id=' + itemId, 
                }
            });

            if (error) {
                throw new Error(error.message);
            }

        } catch (error) {
            showMessage(error.message || '支払い処理中に予期せぬエラーが発生しました。');
            submitButton.disabled = false;
            document.getElementById('button-text').textContent = '¥{{ number_format($item->price) }} を支払う';
        }
    });

    function showMessage(messageText) {
        document.getElementById('payment-message').textContent = messageText;
    }
</script>
</body>
</html>