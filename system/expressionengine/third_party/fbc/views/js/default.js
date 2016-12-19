//	This little script handles clicking and selecting of Facebook Connect member groups.

jQuery(document).ready(function()
{
	populate_select_field();

	jQuery(".fbc_eligible_member_groups").change(function(evt)
	{
		populate_select_field();
	});

	jQuery("#prefs_form").submit(function(evt)
	{
		if ( jQuery("select[name='fbc_member_group']").val() == '' )
		{
			alert('<?=lang("fbc_member_group_required"); ?>');
			return false;
		}
	});
});

function populate_select_field()
{
	//	Get value of pulldown
	var fbc_member_group	= jQuery("select[name='fbc_member_group']").val();

	jQuery("select[name='fbc_member_group']").empty();

	jQuery("select[name='fbc_member_group']").append( '<option value=""><?=lang("select"); ?></option>' );

	jQuery(".fbc_eligible_member_groups").each(function(i,n)
	{
		if ( jQuery(n).attr('checked') === true || jQuery(n).attr('checked') == 'checked' )
		{
			var value		= jQuery(n).val();
			var label		= jQuery(n).next('label').text();
			var selected	= ( value == fbc_member_group ) ? 'selected="selected"': '';
			var option		= '<option value="' + value + '" ' + selected + '>' + label + '</option>';
			jQuery("select[name='fbc_member_group']").append( option );
		}
	});
}