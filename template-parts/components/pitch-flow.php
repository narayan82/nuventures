<?php
/**
 * Shared client-side pitch workflow.
 *
 * @package NuVentures
 */

$pitch_is_embedded = !empty($args['embedded']);
$pitch_close_url   = !empty($args['close_url']) ? $args['close_url'] : home_url('/');
?>

<main
    class="pitch-flow<?php echo $pitch_is_embedded ? ' pitch-flow--embedded' : ''; ?>"
    data-pitch-flow
    data-current-step="1"
    data-close-url="<?php echo esc_url($pitch_close_url); ?>"
    data-pitch-analysis-url="<?php echo esc_url(rest_url('nuventures/v1/analyse-pitch')); ?>"
    data-pitch-session="<?php echo esc_attr(nuventures_create_pitch_session_token()); ?>"
    <?php echo $pitch_is_embedded ? 'data-pitch-embedded aria-hidden="true"' : ''; ?>
>
    <button
        class="pitch-flow__backdrop"
        type="button"
        aria-label="<?php esc_attr_e('Close pitch form', 'nuventures'); ?>"
        data-pitch-close
    ></button>

    <section
        class="pitch-flow__panel"
        role="dialog"
        aria-modal="true"
        aria-labelledby="pitch-flow-title"
        tabindex="-1"
    >
        <h1 class="pitch-flow__screen-reader-title" id="pitch-flow-title">
            <?php esc_html_e('Pitch your idea', 'nuventures'); ?>
        </h1>

        <header class="pitch-flow__header">
            <button
                class="pitch-flow__control pitch-flow__back"
                type="button"
                aria-label="<?php esc_attr_e('Go back to the previous step', 'nuventures'); ?>"
                data-pitch-back
                hidden
            >
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/nu-journey/arrow-dark.svg'); ?>"
                    alt=""
                    width="27"
                    height="27"
                >
            </button>

            <button
                class="pitch-flow__control pitch-flow__close"
                type="button"
                aria-label="<?php esc_attr_e('Close pitch form', 'nuventures'); ?>"
                data-pitch-close
            >
                <img
                    src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/menu/close.svg'); ?>"
                    alt=""
                    width="27"
                    height="27"
                >
            </button>
        </header>

        <div class="pitch-flow__steps">
            <section
                class="pitch-flow__step is-active"
                data-pitch-step="1"
                aria-hidden="false"
            >
                <form class="pitch-flow__form" data-pitch-form="1" novalidate>
                    <div class="pitch-flow__intro">
                        <img
                            class="pitch-flow__launch-image"
                            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/launch.jpg'); ?>"
                            alt=""
                            width="128"
                            height="128"
                            aria-hidden="true"
                        >
                        <p class="pitch-flow__eyebrow"><?php esc_html_e('Start your Pitch', 'nuventures'); ?></p>
                        <p><?php esc_html_e('This guided conversation aims to make this process simple, conversational and effortless!', 'nuventures'); ?></p>
                    </div>

                    <h2 class="pitch-flow__question">
                        <?php esc_html_e('Can we start with a quick introduction? Tell us your Full Name', 'nuventures'); ?>
                    </h2>

                    <div class="pitch-flow__field">
                        <label class="pitch-flow__screen-reader-title" for="pitch-full-name">
                            <?php esc_html_e('Full name', 'nuventures'); ?>
                        </label>
                        <input
                            id="pitch-full-name"
                            name="fullName"
                            type="text"
                            autocomplete="name"
                            placeholder="<?php esc_attr_e('Enter Text', 'nuventures'); ?>"
                            required
                            data-pitch-input
                        >
                        <button class="pitch-flow__send" type="submit" aria-label="<?php esc_attr_e('Continue to mobile number', 'nuventures'); ?>">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/send.svg'); ?>" alt="" width="29" height="29">
                        </button>
                    </div>
                    <p class="pitch-flow__error" data-pitch-error aria-live="polite"></p>
                </form>

                <div class="pitch-flow__voice">
                    <button
                        class="pitch-flow__voice-button pitch-voice-button"
                        type="button"
                        data-voice-target="pitch-full-name"
                        aria-label="<?php esc_attr_e('Hold to record your answer', 'nuventures'); ?>"
                        aria-pressed="false"
                    >
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/microphone.svg'); ?>" alt="" width="24" height="24">
                    </button>
                    <span data-voice-helper><?php esc_html_e('Tap & hold to record', 'nuventures'); ?></span>
                </div>
            </section>

            <section class="pitch-flow__step" data-pitch-step="2" aria-hidden="true" hidden>
                <form class="pitch-flow__form" data-pitch-form="2" novalidate>
                    <div class="pitch-flow__intro">
                        <p class="pitch-flow__eyebrow">
                            <?php esc_html_e('Welcome!', 'nuventures'); ?> <span data-pitch-name>there</span>.
                        </p>
                        <p><?php esc_html_e('Appreciate you taking the time.', 'nuventures'); ?></p>
                    </div>

                    <h2 class="pitch-flow__question">
                        <?php esc_html_e('Can we get your coordinates to start off with?', 'nuventures'); ?>
                    </h2>

                    <p class="pitch-flow__support">
                        <?php esc_html_e('If we lose this connection, we can always reach out.', 'nuventures'); ?>
                    </p>

                    <div class="pitch-flow__field">
                        <label class="pitch-flow__screen-reader-title" for="pitch-mobile">
                            <?php esc_html_e('Mobile number', 'nuventures'); ?>
                        </label>
                        <input
                            id="pitch-mobile"
                            name="mobile"
                            type="tel"
                            inputmode="tel"
                            autocomplete="tel"
                            placeholder="<?php esc_attr_e('Enter Mobile No. (+91...)', 'nuventures'); ?>"
                            required
                            data-pitch-input
                        >
                        <button class="pitch-flow__send" type="submit" aria-label="<?php esc_attr_e('Continue to OTP verification', 'nuventures'); ?>">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/send.svg'); ?>" alt="" width="29" height="29">
                        </button>
                    </div>
                    <p class="pitch-flow__error" data-pitch-error aria-live="polite"></p>
                </form>

                <div class="pitch-flow__voice">
                    <button
                        class="pitch-flow__voice-button pitch-voice-button"
                        type="button"
                        data-voice-target="pitch-mobile"
                        aria-label="<?php esc_attr_e('Hold to record your answer', 'nuventures'); ?>"
                        aria-pressed="false"
                    >
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/microphone.svg'); ?>" alt="" width="24" height="24">
                    </button>
                    <span data-voice-helper><?php esc_html_e('Tap & hold to record', 'nuventures'); ?></span>
                </div>
            </section>

            <section class="pitch-flow__step" data-pitch-step="3" aria-hidden="true" hidden>
                <form class="pitch-flow__form" data-pitch-form="3" novalidate>
                    <div class="pitch-flow__intro">
                        <p class="pitch-flow__eyebrow">
                            <?php esc_html_e('Welcome!', 'nuventures'); ?> <span data-pitch-name>there</span>.
                        </p>
                        <p><?php esc_html_e('Appreciate you taking the time.', 'nuventures'); ?></p>
                    </div>

                    <h2 class="pitch-flow__question">
                        <?php esc_html_e('Can you verify your mobile no. with an OTP?', 'nuventures'); ?>
                    </h2>

                    <div class="pitch-flow__field">
                        <label class="pitch-flow__screen-reader-title" for="pitch-otp">
                            <?php esc_html_e('Four-digit OTP', 'nuventures'); ?>
                        </label>
                        <input
                            id="pitch-otp"
                            name="otp"
                            type="text"
                            inputmode="numeric"
                            autocomplete="one-time-code"
                            maxlength="4"
                            pattern="[0-9]{4}"
                            placeholder="<?php esc_attr_e('Enter 4 Digit OTP', 'nuventures'); ?>"
                            required
                            data-pitch-input
                        >
                        <button class="pitch-flow__send" type="submit" aria-label="<?php esc_attr_e('Verify mocked OTP and continue', 'nuventures'); ?>">
                            <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/send.svg'); ?>" alt="" width="29" height="29">
                        </button>
                    </div>
                    <p class="pitch-flow__error" data-pitch-error aria-live="polite"></p>
                </form>

                <div class="pitch-flow__voice">
                    <button
                        class="pitch-flow__voice-button pitch-voice-button"
                        type="button"
                        data-voice-target="pitch-otp"
                        aria-label="<?php esc_attr_e('Hold to record your answer', 'nuventures'); ?>"
                        aria-pressed="false"
                    >
                        <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/microphone.svg'); ?>" alt="" width="24" height="24">
                    </button>
                    <span data-voice-helper><?php esc_html_e('Tap & hold to record', 'nuventures'); ?></span>
                </div>
            </section>

            <section class="pitch-flow__step pitch-flow__step--deck" data-pitch-step="4" aria-hidden="true" hidden>
                <form class="pitch-flow__form" data-pitch-form="4" data-pitch-upload-choice novalidate>
                    <div class="pitch-flow__intro">
                        <p class="pitch-flow__eyebrow">
                            <?php esc_html_e('Welcome!', 'nuventures'); ?> <span data-pitch-name>there</span>.
                        </p>
                        <p><?php esc_html_e('Appreciate you taking the time.', 'nuventures'); ?></p>
                    </div>

                    <h2 class="pitch-flow__question">
                        <?php esc_html_e('Do you have a pitch deck or presentation about your firm?', 'nuventures'); ?>
                    </h2>

                    <p class="pitch-flow__support">
                        <?php esc_html_e('We’ll extract all the details we need from this, saving you the time taken to enter them manually.', 'nuventures'); ?>
                    </p>

                    <label class="pitch-flow__file">
                        <input
                            name="pitchDeck"
                            type="file"
                            accept=".pdf,application/pdf"
                            data-pitch-file
                        >
                        <span data-pitch-file-label><?php esc_html_e('Select File', 'nuventures'); ?></span>
                    </label>
                    <p class="pitch-flow__error" data-pitch-error aria-live="polite"></p>
                </form>

                <button class="pitch-flow__manual" type="button" data-pitch-manual data-pitch-upload-choice>
                    <?php esc_html_e('No. Enter Manually', 'nuventures'); ?>
                </button>

                <div class="pitch-flow__analysis" data-pitch-analysis-screen hidden aria-live="polite">
                    <div class="pitch-flow__analysis-content">
                        <img
                            class="pitch-flow__analysis-image"
                            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/analysis-gear.png'); ?>"
                            alt=""
                            width="314"
                            height="314"
                        >
                        <h2><?php esc_html_e('Uploading & Analyzing the document', 'nuventures'); ?></h2>
                        <p><?php esc_html_e('This should take less than a minute!', 'nuventures'); ?></p>
                    </div>

                    <button class="pitch-flow__analysis-cancel" type="button" data-pitch-analysis-cancel>
                        <?php esc_html_e('Cancel', 'nuventures'); ?>
                    </button>
                </div>

                <div class="pitch-flow__manual-wizard" data-pitch-manual-wizard hidden>
                    <?php
                    $manual_questions = array(
                        array('companyName', 'What is your company name?', 'text', 0),
                        array('founderCount', 'How many founders are there?', 'number', 0),
                        array('websiteUrl', 'What is your company website?', 'url', 0),
                        array('building', 'What are you building? Please keep it to 140 characters or fewer.', 'text', 140),
                        array('problem', 'What problem does it solve, and for whom? Please keep it to 140 characters or fewer.', 'text', 140),
                        array('raise', 'How much are you raising, and what will it unlock?', 'text', 0),
                        array('moat', 'What makes your solution hard to copy?', 'text', 0),
                    );
                    ?>

                    <?php foreach ($manual_questions as $question_index => $question) : ?>
                        <form
                            class="pitch-flow__form pitch-flow__manual-question<?php echo 0 === $question_index ? ' is-active' : ''; ?>"
                            data-pitch-manual-question="<?php echo esc_attr($question_index); ?>"
                            <?php echo 0 === $question_index ? '' : 'hidden'; ?>
                            novalidate
                        >
                            <div class="pitch-flow__intro">
                                <p class="pitch-flow__eyebrow">
                                    <?php esc_html_e('Welcome!', 'nuventures'); ?> <span data-pitch-name>there</span>.
                                </p>
                                <p data-pitch-manual-intro-note>
                                    <?php esc_html_e('Tell us a little more about your company.', 'nuventures'); ?>
                                </p>
                            </div>

                            <h2 class="pitch-flow__question"><?php echo esc_html($question[1]); ?></h2>

                            <div class="pitch-flow__field">
                                <label class="pitch-flow__screen-reader-title" for="pitch-manual-<?php echo esc_attr($question[0]); ?>">
                                    <?php echo esc_html($question[1]); ?>
                                </label>
                                <input
                                    id="pitch-manual-<?php echo esc_attr($question[0]); ?>"
                                    name="<?php echo esc_attr($question[0]); ?>"
                                    type="<?php echo esc_attr($question[2]); ?>"
                                    <?php echo 'number' === $question[2] ? 'min="1" inputmode="numeric"' : ''; ?>
                                    <?php echo $question[3] ? 'maxlength="' . esc_attr($question[3]) . '"' : ''; ?>
                                    placeholder="<?php esc_attr_e('Enter Text', 'nuventures'); ?>"
                                    required
                                >
                                <button class="pitch-flow__send" type="submit" aria-label="<?php esc_attr_e('Continue to the next question', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/send.svg'); ?>" alt="" width="29" height="29">
                                </button>
                            </div>

                            <?php if ($question[3]) : ?>
                                <p class="pitch-flow__character-count">
                                    <span data-pitch-character-count>0</span>/<?php echo esc_html($question[3]); ?>
                                </p>
                            <?php endif; ?>
                            <p class="pitch-flow__error" data-pitch-error aria-live="polite"></p>

                            <div class="pitch-flow__voice pitch-flow__manual-voice">
                                <button
                                    class="pitch-flow__voice-button pitch-voice-button"
                                    type="button"
                                    data-voice-target="pitch-manual-<?php echo esc_attr($question[0]); ?>"
                                    aria-label="<?php esc_attr_e('Hold to record your answer', 'nuventures'); ?>"
                                    aria-pressed="false"
                                >
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/microphone.svg'); ?>" alt="" width="24" height="24">
                                </button>
                                <span data-voice-helper><?php esc_html_e('Tap & hold to record', 'nuventures'); ?></span>
                            </div>
                        </form>
                    <?php endforeach; ?>

                    <ol class="pitch-flow__manual-progress" aria-label="<?php esc_attr_e('Manual pitch progress', 'nuventures'); ?>">
                        <?php foreach ($manual_questions as $question_index => $question) : ?>
                            <li class="<?php echo 0 === $question_index ? 'is-active' : ''; ?>"></li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </section>

            <section class="pitch-flow__step pitch-flow__step--summary" data-pitch-step="5" aria-hidden="true" hidden>
                <form class="pitch-flow__summary" data-pitch-summary-form novalidate>
                    <div class="pitch-flow__summary-intro">
                        <h2><?php esc_html_e('Hey!', 'nuventures'); ?> <span data-pitch-summary-name>there</span></h2>
                        <p><?php esc_html_e('Your deck was awesome! We captured everything we needed. Can you review it before you submit?', 'nuventures'); ?></p>
                    </div>

                    <h3><?php esc_html_e('Company Details', 'nuventures'); ?></h3>

                    <div class="pitch-flow__summary-list">
                        <div class="pitch-flow__summary-item" data-pitch-summary-item>
                            <div class="pitch-flow__summary-item-heading">
                                <label for="pitch-company-name"><?php esc_html_e('1) Company Name', 'nuventures'); ?></label>
                                <button type="button" data-pitch-edit aria-label="<?php esc_attr_e('Edit company name', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/edit.svg'); ?>" alt="" width="18" height="18">
                                </button>
                            </div>
                            <p data-pitch-summary-value>Aether Technologies</p>
                            <input id="pitch-company-name" name="companyName" type="text" value="Aether Technologies" hidden>
                        </div>
                        <div class="pitch-flow__summary-item" data-pitch-summary-item>
                            <div class="pitch-flow__summary-item-heading">
                                <label for="pitch-founder-count"><?php esc_html_e('2) No. of Founders', 'nuventures'); ?></label>
                                <button type="button" data-pitch-edit aria-label="<?php esc_attr_e('Edit number of founders', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/edit.svg'); ?>" alt="" width="18" height="18">
                                </button>
                            </div>
                            <p data-pitch-summary-value>3</p>
                            <input id="pitch-founder-count" name="founderCount" type="number" min="1" value="3" hidden>
                        </div>
                        <div class="pitch-flow__summary-item" data-pitch-summary-item>
                            <div class="pitch-flow__summary-item-heading">
                                <label for="pitch-company-url"><?php esc_html_e('3) Website URL', 'nuventures'); ?></label>
                                <button type="button" data-pitch-edit aria-label="<?php esc_attr_e('Edit website URL', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/edit.svg'); ?>" alt="" width="18" height="18">
                                </button>
                            </div>
                            <p data-pitch-summary-value>https://aether.io</p>
                            <input
                                id="pitch-company-url"
                                name="websiteUrl"
                                type="text"
                                inputmode="url"
                                autocomplete="url"
                                value="https://aether.io"
                                hidden
                            >
                        </div>
                        <div class="pitch-flow__summary-item pitch-flow__summary-item--long" data-pitch-summary-item>
                            <div class="pitch-flow__summary-item-heading">
                                <label for="pitch-building"><?php esc_html_e('4) What are you building?', 'nuventures'); ?></label>
                                <button type="button" data-pitch-edit aria-label="<?php esc_attr_e('Edit what you are building', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/edit.svg'); ?>" alt="" width="18" height="18">
                                </button>
                            </div>
                            <p data-pitch-summary-value>We are building a decentralized database layer tailored specifically for real-time edge computing applications, bypassing traditional API latencies.</p>
                            <textarea id="pitch-building" name="building" rows="4" hidden>We are building a decentralized database layer tailored specifically for real-time edge computing applications, bypassing traditional API latencies.</textarea>
                        </div>
                        <div class="pitch-flow__summary-item pitch-flow__summary-item--long" data-pitch-summary-item>
                            <div class="pitch-flow__summary-item-heading">
                                <label for="pitch-problem"><?php esc_html_e('5) What problem does it solve? And, for whom?', 'nuventures'); ?></label>
                                <button type="button" data-pitch-edit aria-label="<?php esc_attr_e('Edit the problem and audience', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/edit.svg'); ?>" alt="" width="18" height="18">
                                </button>
                            </div>
                            <p data-pitch-summary-value>IoT and edge device developers struggle with high server round-trip latency. We reduce processing delay from 120ms to under 4ms for critical utility networks.</p>
                            <textarea id="pitch-problem" name="problem" rows="4" hidden>IoT and edge device developers struggle with high server round-trip latency. We reduce processing delay from 120ms to under 4ms for critical utility networks.</textarea>
                        </div>
                        <div class="pitch-flow__summary-item pitch-flow__summary-item--long" data-pitch-summary-item>
                            <div class="pitch-flow__summary-item-heading">
                                <label for="pitch-raise"><?php esc_html_e('6) How much are you raising & what will it unlock?', 'nuventures'); ?></label>
                                <button type="button" data-pitch-edit aria-label="<?php esc_attr_e('Edit fundraising details', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/edit.svg'); ?>" alt="" width="18" height="18">
                                </button>
                            </div>
                            <p data-pitch-summary-value>Raising $1.5M Seed to finalize our edge-routing protocols, scale our devrel team, and acquire our first 50 enterprise node testers.</p>
                            <textarea id="pitch-raise" name="raise" rows="4" hidden>Raising $1.5M Seed to finalize our edge-routing protocols, scale our devrel team, and acquire our first 50 enterprise node testers.</textarea>
                        </div>
                        <div class="pitch-flow__summary-item pitch-flow__summary-item--long" data-pitch-summary-item>
                            <div class="pitch-flow__summary-item-heading">
                                <label for="pitch-moat"><?php esc_html_e('7) What makes your solution hard to copy?', 'nuventures'); ?></label>
                                <button type="button" data-pitch-edit aria-label="<?php esc_attr_e('Edit competitive advantage', 'nuventures'); ?>">
                                    <img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/edit.svg'); ?>" alt="" width="18" height="18">
                                </button>
                            </div>
                            <p data-pitch-summary-value>We hold a pending patent on our dynamic consensus mechanism which allows network syncing with zero permanent ledger storage on end-devices.</p>
                            <textarea id="pitch-moat" name="moat" rows="4" hidden>We hold a pending patent on our dynamic consensus mechanism which allows network syncing with zero permanent ledger storage on end-devices.</textarea>
                        </div>
                    </div>

                    <button class="pitch-flow__summary-submit" type="submit">
                        <?php esc_html_e('Submit', 'nuventures'); ?>
                    </button>
                </form>
            </section>

            <section class="pitch-flow__step pitch-flow__step--thanks" data-pitch-step="6" aria-hidden="true" hidden>
                <div class="pitch-flow__confetti" data-pitch-confetti aria-hidden="true"></div>
                <div class="pitch-flow__thanks">
                    <span class="pitch-flow__success-icon" aria-hidden="true">
                        <img
                            src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/pitch/check_completed.jpg'); ?>"
                            alt=""
                            width="250"
                            height="250"
                        >
                    </span>
                    <h2><span data-pitch-thanks-name>Thank you</span>, <?php esc_html_e('Thank you for submitting!', 'nuventures'); ?></h2>
                    <div class="pitch-flow__thanks-copy">
                        <p><?php esc_html_e("We've received your submission and will review it shortly.", 'nuventures'); ?></p>
                        <p><?php esc_html_e('We try and get back to you within 7 Days of your submission but given our workloads this might take upto 10 Days.', 'nuventures'); ?></p>
                    </div>
                    <button class="pitch-flow__thanks-close" type="button" data-pitch-close>
                        <?php esc_html_e('Close', 'nuventures'); ?>
                    </button>
                </div>
            </section>
        </div>

        <footer class="pitch-flow__footer">
            <p class="pitch-flow__status" aria-live="polite">
                <?php
                printf(
                    /* translators: 1: current pitch step, 2: total pitch steps. */
                    esc_html__('Step %1$d of %2$d', 'nuventures'),
                    1,
                    6
                );
                ?>
            </p>

            <ol class="pitch-flow__progress" aria-hidden="true">
                <?php for ($dot = 1; $dot <= 5; $dot++) : ?>
                    <li class="pitch-flow__progress-dot<?php echo 1 === $dot ? ' is-active' : ''; ?>"></li>
                <?php endfor; ?>
            </ol>
        </footer>
    </section>
</main>
