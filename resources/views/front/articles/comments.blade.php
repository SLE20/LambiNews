@push('styles')
    <style>
        .comments-section {
            max-width: 850px;
            margin: 70px auto 0;
            padding-top: 40px;
            border-top: 1px solid var(--border);
        }

        .comments-count {
            color: var(--muted);
            font-size: 14px;
        }

        .comments-list {
            display: grid;
            gap: 20px;
            margin: 30px 0 45px;
        }

        .comment {
            padding: 22px;
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
        }

        .comment-reply {
            margin-top: 16px;
            margin-left: 38px;
            background: #faf8f2;
        }

        .comment-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .comment-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 42px;
            height: 42px;
            flex-shrink: 0;
            color: var(--black);
            border-radius: 50%;
            background: var(--primary);
            font-weight: 800;
        }

        .comment-author {
            display: block;
            font-weight: 800;
        }

        .comment-date {
            color: var(--muted);
            font-size: 12px;
        }

        .comment-body {
            margin: 0;
            color: #49443b;
            white-space: pre-line;
        }

        .comment-form-wrapper {
            padding: clamp(22px, 5vw, 36px);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            background: white;
            box-shadow: var(--shadow);
        }

        .comment-form-title {
            margin: 0 0 8px;
            font-family: "Playfair Display", Georgia, serif;
            font-size: 27px;
        }

        .comment-form-description {
            margin: 0 0 24px;
            color: var(--muted);
        }

        .comment-form {
            display: grid;
            gap: 18px;
        }

        .comment-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 18px;
        }

        .comment-field {
            display: grid;
            gap: 7px;
        }

        .comment-field label {
            font-size: 14px;
            font-weight: 700;
        }

        .comment-field input,
        .comment-field textarea {
            width: 100%;
            padding: 12px 14px;
            color: var(--text);
            border: 1px solid var(--border);
            border-radius: 9px;
            outline: 0;
            background: #fdfcf9;
            transition:
                border-color 0.2s ease,
                box-shadow 0.2s ease;
        }

        .comment-field input:focus,
        .comment-field textarea:focus {
            border-color: var(--primary);
            box-shadow:
                0 0 0 3px rgba(216, 169, 34, 0.15);
        }

        .comment-field textarea {
            min-height: 150px;
            resize: vertical;
        }

        .comment-submit {
            width: fit-content;
            padding: 12px 22px;
            color: var(--black);
            border: 0;
            border-radius: 999px;
            background: var(--primary);
            cursor: pointer;
            font-weight: 800;
        }

        .comment-submit:hover {
            color: white;
            background: var(--primary-dark);
        }

        .comment-success {
            margin-bottom: 22px;
            padding: 14px 16px;
            color: #14532d;
            border: 1px solid #86efac;
            border-radius: 9px;
            background: #dcfce7;
        }

        .comment-error {
            color: #b91c1c;
            font-size: 13px;
        }

        .comment-honeypot {
            position: absolute;
            left: -9999px;
            width: 1px;
            height: 1px;
            overflow: hidden;
        }

        .comments-closed {
            padding: 22px;
            text-align: center;
            color: var(--muted);
            border: 1px dashed var(--border);
            border-radius: var(--radius);
            background: white;
        }

        @media (max-width: 680px) {
            .comment-form-row {
                grid-template-columns: 1fr;
            }

            .comment-reply {
                margin-left: 18px;
            }

            .comment-submit {
                width: 100%;
            }
        }
    </style>
@endpush

<section class="comments-section" id="commentaires">
    <div class="section-heading">
        <h2 class="section-title">
            Commentaires
        </h2>

        <span class="comments-count">
            {{ $article->approvedComments->count() }}
            commentaire(s)
        </span>
    </div>

    @if($article->approvedComments->isEmpty())
        <div class="empty-state">
            <h2>
                Soyez le premier à réagir
            </h2>

            <p>
                Aucun commentaire approuvé pour le moment.
            </p>
        </div>
    @else
        <div class="comments-list">
            @foreach($article->approvedComments as $comment)
                <article class="comment">
                    <header class="comment-header">
                        <span class="comment-avatar">
                            {{ strtoupper(
                                mb_substr($comment->name, 0, 1)
                            ) }}
                        </span>

                        <div>
                            <span class="comment-author">
                                {{ $comment->name }}
                            </span>

                            <time
                                class="comment-date"
                                datetime="{{ $comment->created_at->toIso8601String() }}"
                            >
                                {{ $comment->created_at->diffForHumans() }}
                            </time>
                        </div>
                    </header>

                    <p class="comment-body">
                        {{ $comment->body }}
                    </p>

                    @foreach($comment->replies as $reply)
                        <article class="comment comment-reply">
                            <header class="comment-header">
                                <span class="comment-avatar">
                                    {{ strtoupper(
                                        mb_substr($reply->name, 0, 1)
                                    ) }}
                                </span>

                                <div>
                                    <span class="comment-author">
                                        {{ $reply->name }}
                                    </span>

                                    <time
                                        class="comment-date"
                                        datetime="{{ $reply->created_at->toIso8601String() }}"
                                    >
                                        {{ $reply->created_at->diffForHumans() }}
                                    </time>
                                </div>
                            </header>

                            <p class="comment-body">
                                {{ $reply->body }}
                            </p>
                        </article>
                    @endforeach
                </article>
            @endforeach
        </div>
    @endif

    @if($article->allow_comments)
        <div class="comment-form-wrapper">
            <h3 class="comment-form-title">
                Laisser un commentaire
            </h3>

            <p class="comment-form-description">
                Votre adresse courriel ne sera pas publiée.
                Tous les commentaires sont modérés.
            </p>

            @if(session('comment_success'))
                <div
                    class="comment-success"
                    role="status"
                >
                    {{ session('comment_success') }}
                </div>
            @endif

            <form
                action="{{ route(
                    'comments.store',
                    $article->slug
                ) }}"
                method="POST"
                class="comment-form"
            >
                @csrf

                <div class="comment-form-row">
                    <div class="comment-field">
                        <label for="comment-name">
                            Nom
                        </label>

                        <input
                            id="comment-name"
                            name="name"
                            type="text"
                            value="{{ old('name') }}"
                            maxlength="100"
                            required
                        >

                        @error('name', 'comment')
                            <span class="comment-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>

                    <div class="comment-field">
                        <label for="comment-email">
                            Adresse courriel
                        </label>

                        <input
                            id="comment-email"
                            name="email"
                            type="email"
                            value="{{ old('email') }}"
                            maxlength="255"
                            required
                        >

                        @error('email', 'comment')
                            <span class="comment-error">
                                {{ $message }}
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="comment-field">
                    <label for="comment-body">
                        Votre commentaire
                    </label>

                    <textarea
                        id="comment-body"
                        name="body"
                        minlength="3"
                        maxlength="2000"
                        required
                    >{{ old('body') }}</textarea>

                    @error('body', 'comment')
                        <span class="comment-error">
                            {{ $message }}
                        </span>
                    @enderror
                </div>

                <div
                    class="comment-honeypot"
                    aria-hidden="true"
                >
                    <label for="website">
                        Ne remplissez pas ce champ
                    </label>

                    <input
                        id="website"
                        name="website"
                        type="text"
                        tabindex="-1"
                        autocomplete="off"
                    >
                </div>

                <button
                    type="submit"
                    class="comment-submit"
                >
                    Envoyer le commentaire
                </button>
            </form>
        </div>
    @else
        <div class="comments-closed">
            Les commentaires sont fermés pour cet article.
        </div>
    @endif
</section>