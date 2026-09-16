{{-- Tableau de bord --}}
<x-backpack::menu-item
    title="Tableau de bord"
    icon="la la-home"
    :link="backpack_url('dashboard')"
/>

{{-- Gestion des articles --}}
<x-backpack::menu-dropdown
    title="Articles"
    icon="la la-newspaper"
>
    <x-backpack::menu-dropdown-item
        title="Tous les articles"
        icon="la la-list"
        :link="backpack_url('article')"
    />

    <x-backpack::menu-dropdown-item
        title="Brouillons"
        icon="la la-file-alt"
        :link="backpack_url('article?status=draft')"
    />

    <x-backpack::menu-dropdown-item
        title="En révision"
        icon="la la-user-edit"
        :link="backpack_url('article?status=review')"
    />

    @if(backpack_user()?->isEditor())
        <x-backpack::menu-dropdown-item
            title="Programmés"
            icon="la la-clock"
            :link="backpack_url('article?status=scheduled')"
        />

        <x-backpack::menu-dropdown-item
            title="Publiés"
            icon="la la-check-circle"
            :link="backpack_url('article?status=published')"
        />

        <x-backpack::menu-dropdown-item
            title="Archivés"
            icon="la la-archive"
            :link="backpack_url('article?status=archived')"
        />
    @endif

    <x-backpack::menu-dropdown-item
        title="Nouvel article"
        icon="la la-plus-circle"
        :link="backpack_url('article/create')"
    />
</x-backpack::menu-dropdown>

{{-- Contenu éditorial --}}
@if(backpack_user()?->isEditor())
    <x-backpack::menu-dropdown
        title="Contenu éditorial"
        icon="la la-folder-open"
    >
        <x-backpack::menu-dropdown-item
            title="Rubriques"
            icon="la la-folder"
            :link="backpack_url('category')"
        />

        <x-backpack::menu-dropdown-item
            title="Mots-clés"
            icon="la la-tags"
            :link="backpack_url('tag')"
        />

        <x-backpack::menu-dropdown-item
            title="Pages"
            icon="la la-file-alt"
            :link="backpack_url('page')"
        />

        <x-backpack::menu-dropdown-item
            title="Commentaires"
            icon="la la-comments"
            :link="backpack_url('comment')"
        />

        <x-backpack::menu-dropdown-item
            title="Messages"
            icon="la la-inbox"
            :link="backpack_url('contact-message')"
        />
    </x-backpack::menu-dropdown>
@endif

{{-- Administration --}}
@if(backpack_user()?->isAdmin())
    <x-backpack::menu-dropdown
        title="Administration"
        icon="la la-cog"
    >
        <x-backpack::menu-dropdown-item
            title="Utilisateurs"
            icon="la la-users"
            :link="backpack_url('user')"
        />

        <x-backpack::menu-dropdown-item
            title="Abonnés"
            icon="la la-envelope"
            :link="backpack_url('subscriber')"
        />

        <x-backpack::menu-dropdown-item
            title="Dons"
            icon="la la-hand-holding-heart"
            :link="backpack_url('donation')"
        />

        <x-backpack::menu-dropdown-item
            title="Publicités"
            icon="la la-ad"
            :link="backpack_url('ad')"
        />

        <x-backpack::menu-dropdown-item
            title="Annonces payantes"
            icon="la la-bullhorn"
            :link="backpack_url('announcement')"
        />

        <x-backpack::menu-dropdown-item
            title="Sondages"
            icon="la la-poll"
            :link="backpack_url('poll')"
        />

        <x-backpack::menu-dropdown-item
            title="Choix de sondage"
            icon="la la-list-ul"
            :link="backpack_url('poll-option')"
        />

    </x-backpack::menu-dropdown>
    <x-backpack::menu-item
    title="Statistiques"
    icon="la la-chart-line"
    :link="backpack_url('statistiques')"
/>
@endif

{{-- Accès au site public --}}
<x-backpack::menu-item
    title="Voir le site"
    icon="la la-external-link-alt"
    :link="url('/')"
    target="_blank"
/>