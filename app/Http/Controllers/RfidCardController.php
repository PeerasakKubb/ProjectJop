<?php

namespace App\Http\Controllers;

use App\Models\RfidCard;
use App\Models\User;
use Illuminate\Http\Request;

class RfidCardController extends Controller
{
    public function index()
    {
        $cards = RfidCard::with('user')->latest()->paginate(20);
        $students = User::where('role', 'student')->orderBy('name')->get();

        return view('rfid.index', compact('cards', 'students'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'card_uid' => 'required|string|max:50|unique:rfid_cards,card_uid',
        ]);

        RfidCard::create($validated);

        return back()->with('success', 'ลงทะเบียนบัตร RFID สำเร็จ');
    }

    public function destroy(RfidCard $rfidCard)
    {
        $rfidCard->delete();

        return back()->with('success', 'ลบบัตร RFID แล้ว');
    }
}
