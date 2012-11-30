<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>

<?php echo $this->register_limit; ?>
<?php if ($this->register): ?>
<form action="<?php echo $this->action; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<?php if ($this->registered): ?>
<p class="message registered"><?php echo $GLOBALS['TL_LANG']['MSC']['youAreRegistered']; ?></p>
<?php echo $this->registered_message; ?>
<input type="submit" class="submit unregister" value="<?php echo $GLOBALS['TL_LANG']['MSC']['eventUnregister']; ?>" />
<?php else: ?>
<p class="message unregistered"><?php echo $GLOBALS['TL_LANG']['MSC']['youAreNotRegistered']; ?></p>
<input type="submit" class="submit register" value="<?php echo $GLOBALS['TL_LANG']['MSC']['eventRegister']; ?>" />
<?php endif; ?>
</div>
</form>
<?php else: ?>
<p class="message closed"><?php echo $GLOBALS['TL_LANG']['MSC']['registrationClosed']; ?></p>
<?php endif; ?>

<?php if($this->listParticipants): ?>
<br /><br />

<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if (count($this->participants)): ?>
<table id="memberregistration_<?php echo $this->id; ?>" class="sortable all_records">
  <thead>
  	<tr>
<?php foreach( $this->editable as $field ): ?>
      <td><?php echo $GLOBALS['TL_LANG']['tl_member'][$field][0]; ?></td>
<?php endforeach; ?>
  </thead>
  <tbody>
<?php foreach( $this->participants as $rowclass => $member ): ?>
    <tr class="<?php echo $member['rowclass']; ?>">
<?php foreach( $this->editable as $field ): ?>
      <td><?php echo $member[$field]; ?></td>
<?php endforeach; ?>
    </tr>
<?php endforeach; ?>
  </tbody>
</table>
<?php else: ?>
<p class="message empty"><?php echo $GLOBALS['TL_LANG']['MSC']['noMembersRegistered']; ?></p>
<?php endif; ?>
<?php endif; ?>
</div>
<!-- indexer::continue -->