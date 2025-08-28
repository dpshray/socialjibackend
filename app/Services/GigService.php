<?php
namespace App\Services;

use App\Constants\Constants;
use App\Models\Gig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class GigService
{
    public $gig = null;
    public $form_data = [];

    public function setformData(Array $data){
        $this->form_data = $data;
        return $this;
    }

    public function store(){
        $this->gig = Gig::create($this->processFormData());
        return $this;
    }

    public function update(Gig $gig){
        $this->gig = $gig;
        $gig->update($this->processFormData());
        return $this;
    }

    public function upsertPricingAndTag($gig = null){
        $gig = $gig ?? $this->gig;
        $pricingData = collect($this->form_data)->only('pricing_tier_id', 'price', 'delivery_time', 'tier_description', 'tier_requirement', 'currency_id');
        // dd(array_keys($pricingData->toArray()));
        // 0 => "pricing_tier_id"
        // 1 => "price"
        // 2 => "delivery_time"
        // 3 => "tier_description"
        // 4 => "currency_id"
        // 5 => "tier_requirement"
        $pricingData = $pricingData
            ->transpose()
            ->map(fn($data) => [
                'pricing_tier_id' => $data[0],
                'price' => $data[1],
                'delivery_time' => Carbon::createFromFormat('d/m/Y', $data[2])->startOfDay()->toDateTimeString(),
                'description' => $data[3],
                'currency_id' => $data[4],
                'requirement' => $data[5],
            ]);
        $gig->gig_pricing()->sync($pricingData->toArray());
        $tag_id = isset($this->form_data['tag_id']) ? $this->form_data['tag_id'] : [];
        $gig->tags()->sync($tag_id);

        if (isset($this->form_data['image'])) {
            $gig->addMedia($this->form_data['image'])->toMediaCollection(Constants::MEDIA_GIG);
        }
    }

    public function processFormData(){
        return [
            'user_id' => Auth::id(),
            'title' => $this->form_data['title'],
            'category' => $this->form_data['category'],
            'description' => $this->form_data['description'],
            'requirements' => $this->form_data['requirements'],
            'features' => $this->form_data['features'],
            'status' => $this->form_data['status'],
            'published_at' => isset($this->form_data['published_at']) ? $this->form_data['published_at'] : Carbon::now(),
        ];
    }
}


?>