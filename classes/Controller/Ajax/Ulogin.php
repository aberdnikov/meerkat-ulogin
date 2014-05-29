<?php
    class Controller_Ajax_Ulogin extends Controller_Ajax {
        function action_free() {
            \Meerkat\StaticFiles\File::need_lib('toastr');
            $id     = Arr::get($_GET, 'id');
            $ulogin = ORM::factory('Ulogin', $id);
            if ($ulogin->loaded() && $ulogin->user_id == \Meerkat\User\Me::id()) {
                $ulogin->user_id = 0;
                $ulogin->save();
                print '$("[data-action=ulogin_free][data-id='.$id.']").closest(".media").hide("slow");';
                print 'toastr.info("Аккаунт удален!");';
            } else {
                print 'toastr.error("Аккаунт не найден или вам не принадлежит!");';
            }
            exit;
        }
    }