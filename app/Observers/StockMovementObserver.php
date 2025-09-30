<?php

namespace App\Observers;

use App\Models\StockMovement;

class StockMovementObserver
{
    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        $product = $stockMovement->product;
        
        if ($stockMovement->type === StockMovement::TYPE_IN) {
            $product->stock += $stockMovement->quantity;
        } else {
            $product->stock -= $stockMovement->quantity;
        }
        
        $product->save();
    }

    /**
     * Handle the StockMovement "updated" event.
     */
    public function updated(StockMovement $stockMovement): void
    {
        // Annuler l'ancien mouvement
        $oldMovement = $stockMovement->getOriginal();
        $product = $stockMovement->product;
        
        if ($oldMovement['type'] === StockMovement::TYPE_IN) {
            $product->stock -= $oldMovement['quantity'];
        } else {
            $product->stock += $oldMovement['quantity'];
        }
        
        // Appliquer le nouveau mouvement
        if ($stockMovement->type === StockMovement::TYPE_IN) {
            $product->stock += $stockMovement->quantity;
        } else {
            $product->stock -= $stockMovement->quantity;
        }
        
        $product->save();
    }

    /**
     * Handle the StockMovement "deleted" event.
     */
    public function deleted(StockMovement $stockMovement): void
    {
        $product = $stockMovement->product;
        
        if ($stockMovement->type === StockMovement::TYPE_IN) {
            $product->stock -= $stockMovement->quantity;
        } else {
            $product->stock += $stockMovement->quantity;
        }
        
        $product->save();
    }

    /**
     * Handle the StockMovement "restored" event.
     */
    public function restored(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "force deleted" event.
     */
    public function forceDeleted(StockMovement $stockMovement): void
    {
        //
    }
}
