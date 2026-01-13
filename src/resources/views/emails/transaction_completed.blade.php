<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
</head>
<body>
    <p>{{ $item->user->name }} 様</p>
    
    <p>出品した商品「{{ $item->name }}」の取引が購入者によって完了されました。</p>
    
    <p>
        <strong>商品名:</strong> {{ $item->name }}<br>
        <strong>購入者:</strong> {{ $buyer->name }} 様
    </p>

    <p>以下のリンクから取引画面を確認し、購入者の評価を行ってください。</p>
    
    <p><a href="{{ route('chat.show', ['item_id' => $item->id]) }}">取引画面へ移動する</a></p>
    
    <hr>
    <p>COACHTECHフリマアプリ</p>
</body>
</html>