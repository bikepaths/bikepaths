<?php if (!defined('HTMLY')) die('HTMLy'); ?>
<?php if (!empty($breadcrumb)): ?>
    <div class="breadcrumb"><?php echo $breadcrumb ?></div>
<?php endif; ?>
<section class="inpost post section" itemprop="blogPost" itemscope="itemscope" itemtype="http://schema.org/BlogPosting">
    <div class="section-inner">
        <div class="content">
            <?php if (login()) { echo tab($p); } ?>
            <div class="item">
                <?php if (!empty($p->image)) { ?>
                    <div class="featured featured-image">
                        <a href="<?php echo $p->url ?>"><img itemprop="image" src="<?php echo $p->image; ?>" alt="<?php echo $p->title ?>"/></a>
                    </div>
                <?php } ?>
                <?php if (!empty($p->video)) { ?>
                    <div class="featured featured-video embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/<?php echo get_video_id($p->video); ?>" frameborder="0" allowfullscreen></iframe>
                    </div>
                <?php } ?>
                <?php if (!empty($p->audio)) { ?>
                    <div class="featured featured-audio embed-responsive embed-responsive-16by9">
                        <iframe class="embed-responsive-item" scrolling="no" frameborder="no" src="https://w.soundcloud.com/player/?url=<?php echo $p->audio;?>&amp;auto_play=false&amp;visual=true"></iframe>
                    </div>
                <?php } ?>
                <?php if (!empty($p->quote)) { ?>
                    <div class="featured featured-quote">
                        <blockquote class="quote"><i class="fa fa-quote-left"></i> <?php echo $p->quote ?> <i class="fa fa-quote-right"></i></blockquote>
                    </div>
                <?php } ?>
                <div class="info text-left">
                    <?php if (!empty($p->link)) { ?>
                        <h1 class="title" itemprop="headline"><a target="_blank" href="<?php echo $p->link ?>"><?php echo $p->title;?> <i class="fa fa-external-link"></i></a></h1>
					<?php } else { ?>
						<h1 class="title" itemprop="headline"><?php echo $p->title;?></h1>
					<?php } ?>
                    <p class="meta">
                        <span class="date" itemprop="datePublished"><?php echo format_date($p->date) ?></span> - <?php echo i18n("Posted_in");?>
                        <span itemprop="articleSection"><?php echo $p->category;?></span> <?php echo i18n("by");?>
                        <span itemprop="author"><a href="<?php echo $p->authorUrl;?>"><?php echo $p->authorName;?></a></span>
                    </p>
                    <div class="voting-module" style="margin: 0px 30px 0px 0px; padding: 10px; background: #f9f9f9; border-radius: 5px; float: right; display: inline-block;">
                        <span id="vote-message-<?php echo $p->slug;?>" style="margin-right: 15px; color: #555; font-style: italic;"></span>
                        <strong>Was this article helpful?</strong> &nbsp;
                        <a href="javascript:void(0)" onclick="vote('<?php echo $p->slug;?>', 'like')" title="Like" style="color: #4CAF50; font-size: 1.2em;"><i class="fa fa-thumbs-up"></i></a> &nbsp;
                        <a href="javascript:void(0)" onclick="vote('<?php echo $p->slug;?>', 'dislike')" title="Dislike" style="color: #F44336; font-size: 1.2em;"><i class="fa fa-thumbs-down"></i></a>
                    </div>
                    <div style="clear: both;"></div>
                </div>
                <div class="desc text-left post-<?php echo $p->date;?>" itemprop="articleBody">
                    <?php echo $p->body; ?>
                </div><!--//desc-->







<div style="margin-top:0px;position:relative;">
	<div class="share pull-right social-logo social">
        <i class="fa fa-share-alt"></i> &nbsp; Share This Post &nbsp; &nbsp;
		<!-- Facebook -->
		<a class="social-logo-facebook" target="_blank" rel="nofollow" title="Share on Facebook" href="https://www.facebook.com/sharer/sharer.php?u=<?php echo $p->url ?>">
			<span class="screen-reader-text"><?php echo $p->title ?></span></a>
		<!-- Twitter -->
		<a class="social-logo-x" target="_blank" rel="nofollow" title="Share on Twitter" href="https://twitter.com/intent/tweet?text=<?php echo $p->title ?>&url=<?php echo $p->url ?>">
			<span class="screen-reader-text"><?php echo $p->title ?></span></a>
		<!-- Telegram -->
		<a class="social-logo-telegram" target="_blank" rel="nofollow" title="Share on Telegram" href="https://t.me/share/url?url=<?php echo $p->url ?>&text=<?php echo $p->title ?>">
			<span class="screen-reader-text"><?php echo $p->title ?></span></a>
		<!-- LinkedIn -->
		<a class="social-logo-linkedin" target="_blank" rel="nofollow" title="Share on LinkedIn" href="https://www.linkedin.com/shareArticle?mini=true&url=<?php echo $p->url ?>&title=<?php echo $p->title ?>">
			<span class="screen-reader-text"><?php echo $p->title ?></span></a>
		<!-- Email -->
		<a class="social-logo-mail" target="_blank" rel="nofollow" title="Share via Email" href="mailto:?subject=<?php echo $p->title ?>&body=<?php echo $p->url ?>">
			<span class="screen-reader-text"><?php echo $p->title ?></span></a>
	</div>
	<div style="clear:both;"></div>
	<span class="tags"><i class="fa fa-tags"></i> <?php echo $p->tag;?></span>
</div>

<div class="article-disclaimer">
    <p><em>Views expressed are the author's own and intended for informational and educational purposes only. Readers are encouraged to conduct their own research before acting on any information presented. Contact the author for errors, inaccuracies or omissions.</em></p>
</div>

<div class="post-retention-block" style="margin: 25px 0 20px 0; padding: 20px; background: #fdfdfd; border: 1px solid #e2e8f0; border-radius: 6px;">
    <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; border-bottom: 1px solid #edf2f7; padding-bottom: 15px; margin-bottom: 15px;">
        <div>
            <strong style="font-size: 1.05em; color: #2d3748;">Was this essay valuable?</strong>
            <span id="end-vote-message-<?php echo $p->slug;?>" style="margin-left: 10px; font-size: 0.9em; font-style: italic;"></span>
        </div>
        <div>
            <button type="button" onclick="vote('<?php echo $p->slug;?>', 'like')" class="btn btn-default btn-sm" style="color: #2e7d32; border-color: #c8e6c9; margin-right: 6px;">
                <i class="fa fa-thumbs-up"></i> Helpful
            </button>
            <button type="button" onclick="vote('<?php echo $p->slug;?>', 'dislike')" class="btn btn-default btn-sm" style="color: #c62828; border-color: #ffcdd2;">
                <i class="fa fa-thumbs-down"></i> Needs Work
            </button>
        </div>
    </div>
    <div>
        <h4 style="margin-top: 0; margin-bottom: 6px; font-weight: 600; color: #1a202c;">Cycles of Change: The Saturday Dispatch</h4>
        <p style="font-size: 0.92em; color: #4a5568; line-height: 1.5; margin-bottom: 12px;">
            A curated weekly digest summarizing the week's three essays on physical infrastructure, autonomous tradecraft, and systemic dignity.
        </p>
        <form action="/blog/signup.php" method="post" style="max-width: 520px; margin-bottom: 10px;">
            <div style="display:none !important; position:absolute; left:-9999px;" aria-hidden="true">
                <label for="website">Leave blank</label>
                <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
            </div>
            <div class="input-group">
                <input type="email" name="email" class="form-control" placeholder="Enter your email" required>
                <span class="input-group-btn">
                    <button class="btn btn-primary" type="submit">Get Weekly Digest</button>
                </span>
            </div>
        </form>
        <div style="font-size: 0.82em; color: #718096; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center;">
            <span>Delivered Saturdays at 9:00 AM EST. Zero spam. One-click unsubscribe.</span>
            <span style="margin-top: 4px;">
                Prefer open protocols? <a href="https://t.me/bikepaths" target="_blank" rel="noopener"><i class="fa fa-telegram"></i> Telegram</a> &bull; <a href="/blog/feed/rss"><i class="fa fa-rss"></i> RSS Feed</a>
            </span>
        </div>
    </div>
</div>





                <?php if (disqus()): ?>
                    <?php echo disqus($p->title, $p->url) ?>
                <?php endif; ?>
                <?php if (disqus_count()): ?>
                    <?php echo disqus_count() ?>
                <?php endif; ?>




                <?php $tags = get_related($p->related, true, config('related.count'));?>
                <?php $char = 100; $total = count($tags); $i = 1; if ($total >= 1) { ?>
                    <div class="related related-posts" style="margin-top:30px;position:relative;">
                        <hr>
                        <h2 class="heading"><?php echo i18n("Related_posts");?></h2>
                        <?php foreach ($tags as $t):?>
                            <div class="item col-md-12">
                                <?php if (strlen(strip_tags($t->title)) > $char) { $relatedTitle = shorten($t->title, $char) . '...';} else {$relatedTitle = $t->title;}?>
                                <h3 class="title"><a href="<?php echo $t->url;?>"><?php echo $relatedTitle;?></a></h3>
                                <div class="content">
                                    <p><?php echo shorten($t->body, 250); ?>... <a class="more-link" href="<?php echo $t->url;?>"><?php echo i18n("read_more");?></a></p><hr>
                                </div><!--//content-->
                            </div>
                            <?php if ($i++ >= config('related.count')) break; ?>
                        <?php endforeach;?>
                        <div style="clear:both;"></div>
                    </div>
                <?php }?>


                <div style="margin-top:0px;position:relative;">
                    <?php if (!empty($next)): ?>
                        <span class="newer"><a href="<?php echo($next['url']); ?>" rel="next"><i class="fa fa-long-arrow-left"></i> <?php echo i18n("Next");?></a></span>
                    <?php endif; ?>
                    <?php if (!empty($prev)): ?>
                        <span class="older pull-right"><a href="<?php echo($prev['url']); ?>" rel="prev"><?php echo i18n("Prev");?> <i class="fa fa-long-arrow-right"></i></a></span>
                    <?php endif; ?>
                    <div style="clear:both;"></div><hr>
                </div>


            </div><!--//item-->
        </div><!--//content-->
    </div><!--//section-inner-->
</section><!--//section-->
<?php if (facebook() || disqus()): ?>
    <section class="comment-wrapper post section">
        <div class="section-inner">
            <div class="content">
               <div id="comments">
                    <h2 class="heading">Comments</h2>
                    <?php if (facebook()): ?>
                        <div class="fb-comments" data-href="<?php echo $p->url ?>" data-numposts="<?php echo config('fb.num') ?>" data-colorscheme="<?php echo config('fb.color') ?>"></div>
                    <?php endif; ?>
                    <?php if (disqus()): ?>
                        <div id="disqus_thread"></div>
                    <?php endif; ?>
                </div>
            </div><!--//content-->
        </div><!--//section-inner-->
    </section><!--//section-->
<?php endif; ?>
<script>
function vote(slug, type) {
    var msgBox = document.getElementById('vote-message-' + slug);
    var endMsgBox = document.getElementById('end-vote-message-' + slug);
    var cookieName = 'voted_' + slug;
    if (document.cookie.split(';').some((item) => item.trim().startsWith(cookieName + '='))) {
        var msg = 'You have already voted on this post.';
        if (msgBox) { msgBox.innerText = msg; msgBox.style.color = '#555'; }
        if (endMsgBox) { endMsgBox.innerText = msg; endMsgBox.style.color = '#555'; }
        return;
    }
    fetch('<?php echo site_url();?>vote/' + slug + '/' + type, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            var msg = 'Thank you for your feedback!';
            if (msgBox) { msgBox.innerText = msg; msgBox.style.color = '#4CAF50'; }
            if (endMsgBox) { endMsgBox.innerText = msg; endMsgBox.style.color = '#4CAF50'; }
        } else {
            var msg = data.message || 'Error';
            if (msgBox) { msgBox.innerText = msg; msgBox.style.color = '#F44336'; }
            if (endMsgBox) { endMsgBox.innerText = msg; endMsgBox.style.color = '#F44336'; }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        var msg = 'An error occurred. Please try again.';
        if (msgBox) { msgBox.innerText = msg; msgBox.style.color = '#F44336'; }
        if (endMsgBox) { endMsgBox.innerText = msg; endMsgBox.style.color = '#F44336'; }
    });
}
</script>
