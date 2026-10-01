<?php

namespace UniqueWorkbench\SharedUi\Workbench\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * For data that belongs to a location (the account app's location id in a
 * `location_id` column, or the column named by $locationColumn): people who
 * don't see every location (workbench()->seesAllLocations()) only get rows
 * for the locations they may see — the account app decides which
 * (assignments, units, a customer's sites). Rows with no location are
 * organization-wide and stay visible.
 *
 * Use with BelongsToOrganization. To see past it (e.g. an owner-only report):
 * Model::withoutGlobalScope('locations').
 */
trait ScopedToLocations
{
    public static function bootScopedToLocations(): void
    {
        static::addGlobalScope('locations', function (Builder $query) {
            $workbench = workbench();
            if (! $workbench->has() || $workbench->seesAllLocations()) {
                return;
            }

            $column = $query->qualifyColumn($query->getModel()->locationColumn());
            $query->where(fn (Builder $q) => $q->whereNull($column)->orWhereIn($column, $workbench->locationIds()));
        });
    }

    public function locationColumn(): string
    {
        return property_exists($this, 'locationColumn') ? $this->locationColumn : 'location_id';
    }
}
