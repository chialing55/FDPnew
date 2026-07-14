@extends('layouts/webapp')

@section('title', $project->title . ' - ' . __('web.title'))

@section('hero')
    <livewire:web.page-background-hero
        :slug="'projects/' . $project->id"
        :page="$heroPage"
        :breadcrumb-parent-label="$breadcrumbParentLabel"
        breadcrumb-parent-url="/projects"
    />
@endsection

@section('content')
    <livewire:web.page-default :slug="'projects/' . $project->id" :page="$project" />
@endsection
