// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 *
 * @module   local_notificationsagent/assigntemplate
 * @copyright 2023, ISYC
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['core/str'], function(str) {
    return {

        init: function() {
            $('#categoriesmodal').on('shown.bs.modal', function() {
                var cat = $('#categories').val();
                if (cat.length != 0) {
                    var categories = JSON.parse(cat);
                    if (categories.length != 0) {
                        categories.forEach(function(categoryId) {
                            $('#checkboxcategory-' + categoryId).prop('checked', true);
                        });
                    }
                }
            });

            $('#categoriesmodal .collapse').on('show.bs.collapse', function () {
                $(this).parents('.listitem-category').removeClass('collapsed');
            });
            $('#categoriesmodal .collapse').on('hide.bs.collapse', function () {
                $(this).parents('.listitem-category').addClass('collapsed');
            });

            /* checkbox */
            $('#categoriesmodal #course-category-select-all').on('click', function(){
                var checkassign = $('#categoriesmodal .category-listing .custom-control-input');
                checkassign.prop('checked', $(this).prop('checked'));
            });
            $('#categoriesmodal .category-listing').on('change', 'input[type=checkbox]', function () {
                var checkssubcategoriescourses = '#category-listing-content-'+$(this).attr("id").replace('checkboxcategory-', '')+' .custom-control-input';
                $('#categoriesmodal .category-listing '+checkssubcategoriescourses).prop('checked', $(this).prop('checked'));
            });

            $('#categoriesmodal #savecategoriesmodal').on('click', function() {
                var data = {};
                data['category'] = [];
                var allCategories = [];
                let mainCategories = $('#categoriesmodal #category-listing-content-0 > li[id^="listitem-category-"]').has('input[id^="checkboxcategory-"]:checked');

                mainCategories.each(function() {
                    let items = $('#' + this.id + ' input[id^="checkboxcategory-"]:checked').map(function() {
                        let id = $(this).attr('id').replace('checkboxcategory-', '');
                        let parent = $(this).data('parent').replace('#category-listing-content-', '');

                        if ($.inArray(id, allCategories) === -1) {
                            allCategories.push(id);
                        }
                            
                        return {id: id, parent: parent};
                    }).get();

                    $.grep(items, function(item) {
                        data['category'].push(item.id);
                    });
                });
                $('#categories').val(data['category']);
                $('#categoriesmodal').modal('hide');
            });
        },
        loopatfirstparent: function(idparent, arrayParents = []){
            var module = this;
            
            if(idparent != '#category-listing-content-0'){
                arrayParents.push(idparent.replace('#category-listing-content-', ''));
                var dataparent = $('#categoriesmodal .category-listing #listitem-category-'+idparent.replace('#category-listing-content-', '')+' > .category-listing-header .custom-control-input').attr('data-parent');
                return module.loopatfirstparent(dataparent, arrayParents);
            }else{
                return arrayParents;
            }
        }
    };
});
