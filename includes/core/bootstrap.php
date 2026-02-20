<?php
if (!defined('ABSPATH')) exit;

if (defined('BCC_BOOTSTRAP_LOADED')) {
    return;
}
define('BCC_BOOTSTRAP_LOADED', true);

/* ======================================================
   CORE
====================================================== */

require_once BCC_INCLUDES_PATH . 'core/visibility.php';
require_once BCC_INCLUDES_PATH . 'core/permissions.php';

/* ======================================================
   DOMAIN (ABSTRACT FIRST)
====================================================== */

require_once BCC_INCLUDES_PATH . 'domain/class-bcc-domain-abstract.php';

require_once BCC_INCLUDES_PATH . 'domain/validator.php';
require_once BCC_INCLUDES_PATH . 'domain/builder.php';
require_once BCC_INCLUDES_PATH . 'domain/dao.php';
require_once BCC_INCLUDES_PATH . 'domain/nft.php';

/* ======================================================
   SYNC
====================================================== */

require_once BCC_INCLUDES_PATH . 'sync/page-to-cpt-sync.php';

/* ======================================================
   AJAX CONTROLLERS
====================================================== */

require_once BCC_INCLUDES_PATH . 'ajax/class-bcc-ajax-inline.php';
require_once BCC_INCLUDES_PATH . 'ajax/class-bcc-ajax-visibility.php';
require_once BCC_INCLUDES_PATH . 'ajax/class-bcc-ajax-gallery.php';
require_once BCC_INCLUDES_PATH . 'ajax/class-bcc-ajax-gallery-meta.php';

/* ======================================================
   RENDERERS (Generic / Reusable)
====================================================== */

require_once BCC_INCLUDES_PATH . 'renderers/class-bcc-field-renderer.php';
require_once BCC_INCLUDES_PATH . 'renderers/class-bcc-repeater-renderer.php';
require_once BCC_INCLUDES_PATH . 'renderers/class-bcc-gallery-renderer.php';
require_once BCC_INCLUDES_PATH . 'renderers/template-functions.php';

/* ======================================================
   HELPERS
====================================================== */
require_once BCC_INCLUDES_PATH . 'repositories/class-bcc-gallery-repository.php';
require_once BCC_INCLUDES_PATH . 'helpers/sync-repair.php';
require_once BCC_INCLUDES_PATH . 'helpers/peepso-page-tabs.php';
require_once BCC_INCLUDES_PATH . 'helpers/data-integrity.php';
require_once BCC_INCLUDES_PATH . 'helpers/class-bcc-options-helper.php';

/* ======================================================
   UI
====================================================== */

require_once BCC_INCLUDES_PATH . 'ui/enqueue.php';
