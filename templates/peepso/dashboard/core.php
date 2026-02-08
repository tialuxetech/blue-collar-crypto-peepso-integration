
    <?php if(get_current_user_id()) { ?>
      <div class="ps-page__edit">
        <?php
     
        $page_users = new PeepSoPageUsers($page->id);
        $page_user = new PeepSoPageUser($page->id);
        ?>

        <div class="ps-page__edit-fields">
          <!-- NAME -->
          <div class="ps-page__edit-field ps-page__edit-field--name ps-js-page-name">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span><?php echo __('Page Name', 'pageso'); ?></span>
                  <span class="ps-page__edit-field-required">*</span>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_name(<?php echo $page->id; ?>, this);">
                    <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-page-name-text">
                  <?php echo $page->name;?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-page-name-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <input type="text" class="ps-input ps-input--sm ps-input--count" maxlength="<?php echo PeepSoPage::$validation['name']['maxlength'];?>" data-maxlength="<?php echo PeepSoPage::$validation['name']['maxlength'];?>" value="<?php echo esc_attr($page->name); ?>">
                  <div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'pageso'); ?>"><?php echo PeepSoPage::$validation['name']['maxlength'];?></span></div>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: NAME -->

          <!--  SLUG -->
          <?php if ($page_user->can('manage_page') && 2 == PeepSo::get_option('pages_slug_edit', 0)) {

          $slug = urldecode($page->slug);
          ?>
          <div class="ps-page__edit-field ps-page__edit-field--slug ps-js-page-slug">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span><?php echo __('Page Slug', 'pageso'); ?></span>
                  <span class="ps-page__edit-field-required">*</span>
                </div>

                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-page-slug-trigger" onclick="ps_page.edit_slug(<?php echo $page->id; ?>, this);">
                    <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-page-slug-text">
                  <?php echo PeepSo::get_page('pages')."<strong>$slug</strong>"; ?>
                </div>
              </div>

              <div class="ps-page__edit-field-form ps-js-page-slug-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <input size="30" class="ps-input ps-input--sm" maxlength="<?php echo PeepSoPage::$validation['name']['maxlength'];?>" data-maxlength="<?php echo PeepSoPage::$validation['name']['maxlength'];?>" value="<?php echo $slug; ?>">
                </div>
                <div class="ps-page__edit-field-desc">
                  <?php
                  echo __('Letters, numbers and dashes are recommended, eg my-amazing-page-123.','pageso') .'<br/>'.__('This field might be automatically adjusted  after editing.','pageso');
                  ?>
                </div>
              </div>
            </div>
          </div><!-- end: SLUG -->
          <?php } ?>

            <!-- DESCRIPTION -->
            <div class="ps-page__edit-field ps-page__edit-field--desc ps-js-page-desc">
                <div class="ps-page__edit-field-row">
                    <div class="ps-page__edit-field-header">
                        <div class="ps-page__edit-field-title">
                            <span><?php echo __('Page Description', 'pageso'); ?></span>
                            <span class="ps-page__edit-field-required">*</span>
                        </div>

                        <?php if ($page_user->can('manage_page')) { ?>
                            <div class="ps-page__edit-field-edit">
                                <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_desc(<?php echo $page->id; ?>, this);">
                                    <?php echo __('Edit','pageso');?>
                                </button>
                            </div>

                            <div class="ps-page__edit-field-actions">
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'pageso'); ?></button>
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                                    <?php echo __('Save', 'pageso'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ps-page__edit-field-static">
                        <?php
                        $description = str_replace("\n","<br/>", $page->description);
                        $description = html_entity_decode($description);
                        if (PeepSo::get_option_new('md_pages_about', 0)) {
                            $description = PeepSo::do_parsedown($description);
                        }
                        ?>

                        <div class="ps-page__edit-field-data">
                            <span class="ps-js-page-desc-text" style="<?php echo empty($page->description) ? 'display:none' : '' ?>"><?php echo stripslashes($description); ?></span>
                            <span class="ps-js-page-desc-placeholder" style="<?php echo empty($page->description) ? '' : 'display:none' ?>"><i><?php echo __('No description', 'pageso'); ?></i></span>
                        </div>
                    </div>

                    <?php if ($page_user->can('manage_page')) { ?>
                        <div class="ps-page__edit-field-form ps-js-page-desc-editor" style="display:none">
                            <div class="ps-input__wrapper">
                                <textarea class="ps-input ps-input--sm ps-input--textarea ps-input--count" rows="10" data-maxlength="<?php echo PeepSoPage::$validation['description']['maxlength'];?>"><?php echo html_entity_decode($page->description); ?></textarea>
                                <div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'pageso'); ?>"><?php echo PeepSoPage::$validation['description']['maxlength'];?></span></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- end: DESCRIPTION -->


            <?php if(PeepSo::get_option_new('pages_rules_enabled')) { ?>
            <!-- RULES -->
            <div class="ps-page__edit-field ps-page__edit-field--rules ps-js-page-rules">
                <div class="ps-page__edit-field-row">
                    <div class="ps-page__edit-field-header">
                        <div class="ps-page__edit-field-title">
                            <span><?php echo __('Page Rules', 'pageso'); ?></span>
<!--                            <span class="ps-page__edit-field-required">*</span>-->
                        </div>

                        <?php if ($page_user->can('manage_page')) { ?>
                            <div class="ps-page__edit-field-edit">
                                <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_rules(<?php echo $page->id; ?>, this);">
                                    <?php echo __('Edit','pageso');?>
                                </button>
                            </div>

                            <div class="ps-page__edit-field-actions">
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'pageso'); ?></button>
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                                    <?php echo __('Save', 'pageso'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ps-page__edit-field-static">
                        <?php
                        $rules = str_replace("\n","<br/>", $page->rules);
                        $rules = html_entity_decode($rules);
                        if (PeepSo::get_option_new('md_pages_rules', 0)) {
                            $rules = PeepSo::do_parsedown($rules);
                        }
                        ?>

                        <div class="ps-page__edit-field-data">
                            <span class="ps-js-page-rules-text" style="<?php echo empty($page->rules) ? 'display:none' : '' ?>"><?php echo stripslashes($rules); ?></span>
                            <span class="ps-js-page-rules-placeholder" style="<?php echo empty($page->rules) ? '' : 'display:none' ?>"><i><?php echo __('No rules', 'pageso'); ?></i></span>
                        </div>
                    </div>

                    <?php if ($page_user->can('manage_page')) { ?>
                        <div class="ps-page__edit-field-form ps-js-page-rules-editor" style="display:none">
                            <div class="ps-input__wrapper">
                                <textarea class="ps-input ps-input--sm ps-input--textarea ps-input--count" rows="10" data-maxlength="<?php echo PeepSoPage::$validation['rules']['maxlength'];?>"><?php echo html_entity_decode($page->rules); ?></textarea>
                                <div class="ps-form__chars-count"><span class="ps-js-limit ps-tip ps-tip--inline" aria-label="<?php echo __('Characters left', 'pageso'); ?>"><?php echo PeepSoPage::$validation['rules']['maxlength'];?></span></div>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- end: RULES -->
            <?php } ?>

          <?php do_action('peepso_action_render_page_settings_form_before'); ?>

          <?php if(PeepSo::get_option('pages_categories_enabled', FALSE)) { ?>
          <!-- CATEGORIES -->
          <div class="ps-page__edit-field ps-page__edit-field--cats ps-js-page-cat">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span><?php
                  $page_categories = PeepSoPageCategoriesPages::get_categories_for_page($page->id);

                  echo _n('Category', 'Categories', count($page_categories), 'pageso'); ?></span>
                  <span class="ps-page__edit-field-required">*</span>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_cats(<?php echo $page->id; ?>, this);">
                      <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-cancel"><?php echo __('Cancel', 'pageso'); ?></button>
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-page-cat-text">
                  <?php
                    $page_categories_html = array();
                    foreach ($page_categories as $PeepSoPageCategory) {
                      echo "<a href=\"{$PeepSoPageCategory->get_url()}\">{$PeepSoPageCategory->name}</a>";
                    }
                  ?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-page-cat-editor" style="display:none">
                <div class="ps-input__wrapper ps-checkbox__grid">
                  <?php

                  $multiple_enabled = (PeepSo::get_option_new('pages_categories_multiple_max') > 1);
                  $input_type = ($multiple_enabled) ? 'checkbox' : 'radio';
                  $PeepSoPageCategories = new PeepSoPageCategories(FALSE, TRUE);
                  $categories = $PeepSoPageCategories->categories;

                  if (count($categories)) {
                      foreach ($categories as $id => $category) {
                          $checked = '';
                          if (isset($page_categories[$id])) {
                              $checked = 'checked="checked"';
                          }
                          echo sprintf('<div class="ps-checkbox"><input class="ps-checkbox__input" %s type="%s" id="category_' . $id . '" name="category_id" value="%d"><label class="ps-checkbox__label" for="category_' . $id . '">%s</label></div>', $checked, $input_type, $id, $category->name);
                      }
                  }

                  ?>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: CATEGORIES -->
          <?php } ?>

          <?php do_action('peepso_action_render_page_settings_form_after'); ?>

          <?php if(!$page->is_secret) { ?>
          <!-- JOIN BUTTON -->
          <div class="ps-page__edit-field ps-page__edit-field--join ps-js-page-is_joinable">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span>
                    <?php
                      if($page->is_open) { echo __('Enable "like" button', 'pageso'); }
                      if($page->is_closed) { echo __('Enable "Request To Like" button', 'pageso'); }
                    ?>
                  </span>
                  <div class="ps-page__edit-field-note">
                    <?php echo __('Has no effect on Site Administrators','pageso'); ?>
                  </div>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_property(this, <?php echo $page->id; ?>, 'is_joinable');">
                      <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-text">
                  <?php echo ($page->is_joinable) ? __('Yes', 'pageso') : __('No', 'pageso');?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_joinable" class="ps-input ps-input--sm ps-input--select">
                    <option value="1"><?php echo __('Yes', 'pageso');?></option>
                    <option value="0" <?php if(FALSE == $page->is_joinable) { echo "selected";}?>><?php echo __('No', 'pageso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: JOIN BUTTON -->
          <?php } ?>

          <!-- INVITE BUTTON -->
          <div class="ps-page__edit-field ps-page__edit-field--invite ps-js-page-is_invitable">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span>
                    <?php echo __('Enable "Invite" button', 'pageso'); ?>
                  </span>
                  <div class="ps-page__edit-field-note">
                    <?php echo __('Has no effect on Owner, Managers and Site Administrators','pageso'); ?>
                  </div>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_property(this, <?php echo $page->id; ?>, 'is_invitable');">
                      <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-text">
                  <?php echo ($page->is_invitable) ? __('Yes', 'pageso') : __('No', 'pageso');?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_invitable" class="ps-input ps-input--sm ps-input--select">
                    <option value="1"><?php echo __('Yes', 'pageso');?></option>
                    <option value="0" <?php if(FALSE == $page->is_invitable) { echo "selected";}?>><?php echo __('No', 'pageso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: INVITE BUTTON -->

            <?php if(PeepSo::get_option_new('pages_members_tab_override')) { ?>
            <!-- FOLLOWERS TAB -->
            <div class="ps-page__edit-field ps-page__edit-field--members_tab ps-js-page-members_tab">
                <div class="ps-page__edit-field-row">
                    <div class="ps-page__edit-field-header">
                        <div class="ps-page__edit-field-title">
                  <span>
                    <?php echo __('Followers tab', 'pageso'); ?>
                  </span>
                            <div class="ps-page__edit-field-note">
                                <?php echo __('Has no effect on Owner, Managers and Site Administrators','pageso'); ?>
                            </div>
                        </div>

                        <?php if ($page_user->can('manage_page')) { ?>
                            <div class="ps-page__edit-field-edit">
                                <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_property(this, <?php echo $page->id; ?>, 'members_tab');">
                                    <?php echo __('Edit','pageso');?>
                                </button>
                            </div>

                            <div class="ps-page__edit-field-actions">
                                <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                                <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                                    <?php echo __('Save', 'pageso'); ?>
                                </button>
                            </div>
                        <?php } ?>
                    </div>

                    <div class="ps-page__edit-field-static">
                        <div class="ps-page__edit-field-data ps-js-text">
                            <?php echo ($page->members_tab) ? __('Yes', 'pageso') : __('No', 'pageso');?>
                        </div>
                    </div>

                    <?php if ($page_user->can('manage_page')) { ?>
                        <div class="ps-page__edit-field-form ps-js-editor" style="display:none">
                            <div class="ps-input__wrapper">
                                <select name="members_tab" class="ps-input ps-input--sm ps-input--select">
                                    <option value="1"><?php echo __('Yes', 'pageso');?></option>
                                    <option value="0" <?php if(FALSE == $page->members_tab) { echo "selected";}?>><?php echo __('No', 'pageso');?></option>
                                </select>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            </div><!-- end: FOLLOWERS TAB -->
            <?php } ?>

          <!-- DISABLE COMMENTS / REACTIONS / LIKE -->
          <div class="ps-page__edit-field ps-page__edit-field--interactable ps-js-page-is_interactable">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span>
                    <?php echo __('Disable likes/comments', 'pageso'); ?>
                  </span>
                  <div class="ps-page__edit-field-note">
                    <?php echo __('Has no effect on Owner, Managers and Site Administrators','pageso'); ?>
                  </div>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_property(this, <?php echo $page->id; ?>, 'is_interactable');">
                      <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-text">
                  <?php echo ($page->is_interactable) ? __('Yes', 'pageso') : __('No', 'pageso');?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_interactable" class="ps-input ps-input--sm ps-input--select">
                      <option value="1"><?php echo __('Yes', 'pageso');?></option>
                      <option value="0" <?php if(FALSE == $page->is_interactable) { echo "selected";}?>><?php echo __('No', 'pageso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: DISABLE COMMENTS / REACTIONS / LIKE -->

          <!-- ALLOWED NON-FOLLOWER ACTIONS COMMENTS / REACTINS / LIKE -->
            <?php if($page->is_open) { ?>
          <div class="ps-page__edit-field ps-page__edit-field--allowed_non_member_actions ps-js-page-is_allowed_non_member_actions">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span>
                    <?php echo __('Allowed non-follower actions', 'pageso'); ?>
                  </span>
                    <div class="ps-page__edit-field-note">
                        <?php echo __('Has no effect if the setting above is set to "yes"','pageso'); ?>
                    </div>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_property(this, <?php echo $page->id; ?>, 'is_allowed_non_member_actions');">
                      <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                    <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                    <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-text">
                  <?php
                  switch ($page->is_allowed_non_member_actions) {
                    case 1:
                      echo __('Reactions', 'pageso');
                      break;
                    case 2:
                      echo __('Comments', 'pageso');
                      break;
                    case 3:
                      echo __('Reactions and comments', 'pageso');
                      break;

                    default:
                      echo __('Nothing (default)', 'pageso');;
                      break;
                  }
                  ?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_allowed_non_member_actions" class="ps-input ps-input--sm ps-input--select">
                      <option value="0"><?php echo __('Nothing (default)', 'pageso');?></option>
                      <option value="1" <?php if(1 == $page->is_allowed_non_member_actions) { echo "selected";}?>><?php echo __('Reactions', 'pageso');?></option>
                      <option value="2" <?php if(2 == $page->is_allowed_non_member_actions) { echo "selected";}?>><?php echo __('Comments', 'pageso');?></option>
                      <option value="3" <?php if(3 == $page->is_allowed_non_member_actions) { echo "selected";}?>><?php echo __('Reactions and comments', 'pageso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div>
          <?php } ?>
          <!-- end: ALLOWED NON-MEMBER ACTIONS COMMENTS / REACTINS / LIKE -->

          <!-- DISABLE NEW MEMBER NOTIFICATIONS -->
          <div class="ps-page__edit-field ps-page__edit-field--muted ps-js-page-is_join_muted">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span>
                    <?php echo __('Disable new followers notifications', 'pageso'); ?>
                  </span>
                  <div class="ps-page__edit-field-note">
                    <?php echo __('Owners & Managers will not receive notifications about new followers','pageso'); ?>
                  </div>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_property(this, <?php echo $page->id; ?>, 'is_join_muted');">
                      <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-text">
                  <?php echo ($page->is_join_muted) ? __('Yes', 'pageso') : __('No', 'pageso');?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_join_muted" class="ps-input ps-input--sm ps-input--select">
                      <option value="1"><?php echo __('Yes', 'pageso');?></option>
                      <option value="0" <?php if(FALSE == $page->is_join_muted) { echo "selected";}?>><?php echo __('No', 'pageso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: DISABLE NEW MEMBER NOTIFICATIONS -->

          <?php if ($page->is_closed) {?>
          <!-- AUTO ACCEPT MEMBER -->
          <div class="ps-page__edit-field ps-page__edit-field--muted ps-js-page-is_auto_accept_join_request">
            <div class="ps-page__edit-field-row">
              <div class="ps-page__edit-field-header">
                <div class="ps-page__edit-field-title">
                  <span>
                    <?php echo __('Automatically accept join requests', 'pageso'); ?>
                  </span>
                  <div class="ps-page__edit-field-note">
                    <?php echo __('User immediately becomes a new member after click "join" button','pageso'); ?>
                  </div>
                </div>

                <?php if ($page_user->can('manage_page')) { ?>
                <div class="ps-page__edit-field-edit">
                  <button class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-edit" onclick="ps_page.edit_property(this, <?php echo $page->id; ?>, 'is_auto_accept_join_request');">
                      <?php echo __('Edit','pageso');?>
                  </button>
                </div>

                <div class="ps-page__edit-field-actions">
                  <button type="button" class="ps-btn ps-btn--xs ps-btn--app ps-js-btn-cancel"><?php echo __('Cancel', 'pageso'); ?></button>

                  <button type="button" class="ps-btn ps-btn--xs ps-btn--action ps-js-btn-submit">
                      <img src="<?php echo PeepSo::get_asset('images/ajax-loader.gif'); ?>" class="ps-js-loading" alt="loading" style="display:none" />
                      <?php echo __('Save', 'pageso'); ?>
                  </button>
                </div>
                <?php } ?>
              </div>

              <div class="ps-page__edit-field-static">
                <div class="ps-page__edit-field-data ps-js-text">
                  <?php echo ($page->is_auto_accept_join_request) ? __('Yes', 'pageso') : __('No', 'pageso');?>
                </div>
              </div>

              <?php if ($page_user->can('manage_page')) { ?>
              <div class="ps-page__edit-field-form ps-js-editor" style="display:none">
                <div class="ps-input__wrapper">
                  <select name="is_auto_accept_join_request" class="ps-input ps-input--sm ps-input--select">
                      <option value="1"><?php echo __('Yes', 'pageso');?></option>
                      <option value="0" <?php if(FALSE == $page->is_auto_accept_join_request) { echo "selected";}?>><?php echo __('No', 'pageso');?></option>
                  </select>
                </div>
              </div>
              <?php } ?>
            </div>
          </div><!-- end: AUTO ACCEPT MEMBER -->
          <?php } ?>

        </div>
      </div>
    <?php } ?>
  </div>
</div>
<?php

if(get_current_user_id()) {
    PeepSoTemplate::exec_template('activity' ,'dialogs');
}
