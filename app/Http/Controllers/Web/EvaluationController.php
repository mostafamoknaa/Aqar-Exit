<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreEvaluationRequest;
use App\Models\Evaluation;
use App\Models\Setting;
use App\Services\ExitCalculatorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EvaluationController extends Controller
{
    public function create(): View
    {
        return view('web.evaluate');
    }

    public function calculate(Request $request, ExitCalculatorService $calculator): View
    {
        $validated = $request->validate([
            'total_price' => ['required', 'numeric', 'min:1'],
            'paid_amount' => ['required', 'numeric', 'min:0', 'lte:total_price'],
            'cancellation_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return view('web.evaluate', ['result' => $calculator->calculate((int) $validated['total_price'], (int) $validated['paid_amount'], isset($validated['cancellation_percent']) ? (float) $validated['cancellation_percent'] : null), 'input' => $validated]);
    }

    public function store(StoreEvaluationRequest $request): RedirectResponse
    {
        Evaluation::create([
            ...$request->safe()->except(['name', 'phone']),
            'user_id' => auth()->id(),
            'name' => $request->input('name', auth()->user()?->name),
            'phone' => $request->input('phone', auth()->user()?->phone),
            'cancellation_percent' => $request->input('cancellation_percent', Setting::get('default_cancellation_percent', config('aqar.default_cancellation_percent'))),
        ]);

        return redirect()->route('web.evaluate')->with('success', 'Request received — our team will prepare your free exit report and contact you.');
    }
}
