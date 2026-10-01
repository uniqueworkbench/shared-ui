<?php

namespace UniqueWorkbench\SharedUi\Workbench\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * For every table of this app's own data: an `organization_id` column (the
 * account app's organization id), filled in on create and filtered on every
 * query from the signed-in user's organization (workbench()).
 *
 * Outside a signed-in request there's no organization (Feature API calls,
 * queued jobs, commands, the scheduler): nothing is filtered there, so name
 * it yourself — Model::forOrganization($id) — and pass organization_id into jobs.
 */
trait BelongsToOrganization
{
    public static function bootBelongsToOrganization(): void
    {
        static::addGlobalScope('organization', function (Builder $query) {
            if ($organizationId = workbench()->organizationId()) {
                $query->where($query->qualifyColumn('organization_id'), $organizationId);
            }
        });

        static::creating(function (Model $model) {
            $model->organization_id ??= workbench()->organizationId();

            if (! $model->organization_id) {
                throw new \LogicException(class_basename($model) . ' needs an organization_id: there is no signed-in organization here, so set it.');
            }
        });
    }

    /** Rows of one organization, whoever is signed in (Feature API, jobs, commands) */
    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->withoutGlobalScope('organization')->where($query->qualifyColumn('organization_id'), $organizationId);
    }
}
