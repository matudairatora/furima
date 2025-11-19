<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Item;
use App\Models\Comment;
use App\Http\Requests\CommentRequest;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use App\Models\ShippingAddress;
use App\Models\SoldItem;


class ItemController extends Controller
{
   public function show($id){

    
    $item = Item::with(['comments.user', 'condition', 'categories', 'favorites', 'soldItem'])
            ->findOrFail($id);
    return view('auth.itemsell', compact('item'));
   } 
   
   public function addComment(CommentRequest $request, $id)
    {
        $commentData = [
            
            'item_id' => $id, 
            'user_id' => Auth::id(),
            'comment' => $request->input('comment'), 
        ];

        
        $comment = Comment::create($commentData);

        return redirect()->route('item.show',['item_id' => $id]);
    }

    public function toggleFavorite(Request $request, $itemId)
    {
        $user = Auth::user();
        $item = Item::findOrFail($itemId);

        
        $user->favorites()->toggle($item->id);

        return back(); 
    }

public function showPurchaseForm($itemId)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $item = Item::findOrFail($itemId);
        $user = Auth::user();

        
        $shipping = ShippingAddress::where('user_id', $user->id)->where('item_id', $itemId)->first();

        
        if ($shipping) {
            $userAddress = [
                'postcode' => $shipping->postcode,
                'address_line' => $shipping->address . ($shipping->building ? ' ' . $shipping->building : ''),
            ];
        } elseif ($user->mypage) {
            $userAddress = [
                'postcode' => $user->mypage->postcode,
                'address_line' => $user->mypage->address . ($user->mypage->building ? ' ' . $user->mypage->building : ''),
            ];
        } else {
             $userAddress = [
                'postcode' => '',
                'address_line' => '',
            ];
        }

        return view('auth.purchase', [
            'item' => $item,
            'userAddress' => $userAddress,
        ]);
    }


   public function showAddressEditForm(Request $request)
    {
        $itemId = $request->query('itemId');
        $user = Auth::user();

        
        $address = null;
        if ($itemId) {
            $address = ShippingAddress::where('user_id', $user->id)->where('item_id', $itemId)->first();
        }
        
        
        if (!$address) {
            $address = $user->mypage; 
        }

        return view('auth.address_edit', [
            'address' => $address, 
            'itemId' => $itemId,
        ]);
    }

public function updateAddress(AddressRequest $request)
    {
        $user = Auth::user();
        $itemId = $request->input('item_id');
        $updateData = $request->validated();
        $updateData['building'] = $request->input('building');

        
        if ($itemId) {
            ShippingAddress::updateOrCreate(
                ['user_id' => $user->id, 'item_id' => $itemId],
                $updateData
            );
            
            return redirect()->route('item.purchase', ['itemId' => $itemId]);
        }

        
        $mypage = $user->mypage;
        if (is_null($mypage)) {
            $user->mypage()->create($updateData);
        } else {
            $mypage->fill($updateData)->save();
        }

        return redirect()->route('auth.mypage');
    }
    public function processPurchase(PurchaseRequest $request, $itemId)
    {   
        
        if (!Auth::check()) {
            return redirect()->route('login');
        }
        
        $item = Item::findOrFail($itemId);

        
        if ($item->is_sold) {
            return back()->with('error', 'この商品は既に売り切れました。');
        }       


       
        $paymentMethod = $request->input('payment_method');
        
        switch ($paymentMethod) {
            case 'convenience':
                SoldItem::create([
                'item_id' => $item->id,
                'user_id' => Auth::id(),
                ]);
                
                return redirect()->route('auth.index');
            case 'card':
                
                return redirect()->route('checkout', [
                    'itemId' => $item->id,
                ]);
            
            default:
                
                return redirect()->route('auth.index');
        }
    }
    
    public function afterpurchasecard()
    {
        
        return view('item.aftercard');
    }

}
