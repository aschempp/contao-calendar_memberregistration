
<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if(count($this->events)): ?>
<form action="<?php echo $this->action; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<table cellspacing="0" cellpadding="0" summary="Event registrations">
<?php foreach($this->events as $dayEvents): foreach($dayEvents as $events): foreach($events as $event): ?>
<tr>
	<td><input type="checkbox" name="events[]" value="<?php echo $event['id']; ?>" /></td>
	<td><?php echo $event['title']; ?></td>
	<td><?php echo $event['date']; ?> <?php echo $event['time']; ?></>
</tr>
<?php endforeach; endforeach; endforeach; ?>
</table>
<div class="submit_container"><input type="submit" class="submit" value="Unregister" /></div>
</div>
</form>
<?php else: ?>
<p class="message empty">You are currently not subscribed to any events.</p>
<?php endif; ?>

</div>
<!-- indexer::continue -->