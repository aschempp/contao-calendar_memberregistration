
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<?php echo $this->register_limit; ?>
<?php if ($this->register): ?>
<form action="<?php echo $this->action; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<?php if ($this->registered): ?>
<p class="message">You are registered for this event.</p>
<?php echo $this->registered_message; ?>
<input type="image" src="tl_files/template/images/signoff_btn.jpg" class="submit" value="Unregister" />
<?php else: ?>
<p class="message">You are not registered for this event.</p>
<input type="image" src="tl_files/template/images/anmeldung_btn.jpg" class="submit" value="Register" />
<?php endif; ?>
</div>
</form>
<?php else: ?>
Registration for this event is closed.
<?php endif; ?>
<br /><br />

<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if (count($this->participants)): ?>
<table id="memberregistration_<?php echo $this->id; ?>" cellspacing="0" cellpadding="0" border="0" class="sortable all_records" summary="Event registrations">
  <thead>
  	<tr>
  	  <th>Lastname</th>
  	  <th>Firstname</th>
  	  <th>Company</th>
  </thead>
  <tbody>
<?php foreach( $this->participants as $rowclass => $member ): ?>
    <tr class="<?php echo $member['rowclass']; ?>">
      <td><?php echo $member['lastname']; ?></td>
      <td><?php echo $member['firstname']; ?></td>
      <td><?php echo $member['company']; ?></td>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p class="message empty">No member are registered for this event.</p>
<?php endif; ?>

</div>
<!-- indexer::continue -->