<div>
    {{-- El texto se edita en /admin/legal (el inicial está en resources/legal/privacidad.md). Revisarlo frente a la LOPDP. --}}
    <x-ui.page title="Política de" accent="privacidad" eyebrow="Legal" lede="Versión vigente desde el {{ \App\Support\Content::versionDate(config('marketplace.terms_version')) }}" />
    <section class="mx-auto max-w-[84rem] px-5 py-12 sm:px-8 lg:py-16">
        <div class="ui-prose">
            {{ \App\Support\Content::markdown(config('content.legal.privacy')) }}
        </div>
    </section>
</div>
