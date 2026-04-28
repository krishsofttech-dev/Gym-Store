<?php

namespace App\Http\Controllers;

use App\Models\Coupon;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function apply(Request $request)
    {
        $request->validate(['code' => ['required', 'string']]);

        $coupon = Coupon::findByCode(strtoupper($request->code));

        if (! $coupon || ! $coupon->isValid()) {
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Invalid or expired coupon code.'], 422);
            }
            return back()->with('error', 'Invalid or expired coupon code.');
        }

        session(['coupon' => $coupon->code]);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Coupon \"{$coupon->code}\" applied!",
                'coupon'  => $coupon->code,
            ]);
        }

        return back()->with('success', "Coupon \"{$coupon->code}\" applied!");
    }

    public function remove(Request $request)
    {
        session()->forget('coupon');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Coupon removed.']);
        }

        return back()->with('success', 'Coupon removed.');
    }
}