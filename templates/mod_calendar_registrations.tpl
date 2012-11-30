<!-- indexer::stop -->
<div class="<?php echo $this->class; ?> block"<?php echo $this->cssID; ?><?php if ($this->style): ?> style="<?php echo $this->style; ?>"<?php endif; ?>>
<?php if ($this->headline): ?>

<<?php echo $this->hl; ?>><?php echo $this->headline; ?></<?php echo $this->hl; ?>>
<?php endif; ?>

<?php if(count($this->events)): ?>
<form action="<?php echo $this->action; ?>" method="post">
<div class="formbody">
<input type="hidden" name="FORM_SUBMIT" value="<?php echo $this->formSubmit; ?>" />
<input type="hidden" name="REQUEST_TOKEN" value="{{request_token}}" />
<table class="unsubscribe">
<?php foreach($this->events as $dayEvents): foreach($dayEvents as $events): foreach($events as $event): ?>
<tr>
	<td class="col_0 col_first"><input type="checkbox" name="events[]" value="<?php echo $event['id']; ?>" /></td>
	<td class="col_1"><?php echo $event['title']; ?></td>
	<td class="col_2 col_last"><?php echo $event['date']; ?> <?php echo $event['time']; ?></td>
</tr>
<?php endforeach; endforeach; endforeach; ?>
</table>
<div class="submit_container"><input type="submit" class="submit" value="<?php echo $GLOBALS['TL_LANG']['MSC']['unregisterSelected']; ?>" /></div>
</div>
</form>
<?php else: ?>
<p class="message empty"><?php echo $GLOBALS['TL_LANG']['MSC']['notSubscribed']; ?></p>
<?php endif; ?>

</div>
<!-- indexer::continue -->