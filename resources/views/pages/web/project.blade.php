@extends('layouts/webapp')

@section('title', $project->title . ' - ' . __('web.title'))

@section('hero')
    <livewire:web.page-background-hero :slug="'projects/' . $project->id" :page="$heroPage" />
@endsection

@section('content')
    <livewire:web.page-default :slug="'projects/' . $project->id" :page="$project" />
@endsection
