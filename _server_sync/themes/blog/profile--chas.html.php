<?php if (!defined('HTMLY')) die('HTMLy'); ?>
<?php if (!empty($breadcrumb)): ?>
    <div class="breadcrumb"><?php echo $breadcrumb ?></div>
<?php endif; ?>
<section class="inprofile post section">
    <div class="section-inner">
        <div class="content"> 
            <div class="item" itemtype="http://schema.org/Person" itemscope="itemscope" itemprop="Person">
                <h2 class="title" itemprop="name"><?php echo $author->name ?></h2>
                <div class="desc text-left" itemprop="description">                                    
                    <?php echo $author->about; ?>
                </div><!--//desc-->

                <!-- Direct Reader Engagement: Anonymous Topic Suggestion & Inquiry -->
                <div class="inquiry-card" style="margin: 30px 0; padding: 20px 24px; background: #fdfdfd; border: 1px solid #e2e8f0; border-left: 4px solid #2b6cb0; border-radius: 6px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                    <h4 style="margin-top: 0; margin-bottom: 8px; font-weight: 700; color: #2d3748; font-size: 1.15em;">
                        <i class="fa fa-lightbulb-o" style="color: #2b6cb0; margin-right: 6px;"></i> Suggest a Research Topic or Direct Inquiry
                    </h4>
                    <p style="font-size: 0.92em; color: #4a5568; margin-bottom: 14px; line-height: 1.5;">
                        Have a question, challenge, or topic you would like Charles to explore in an upcoming essay or SDI dispatch? Submissions are completely anonymous.
                    </p>
                    <form id="author-inquiry-form" onsubmit="submitInquiry(event)">
                        <!-- Honeypot anti-spam -->
                        <input type="text" name="website" style="display:none;" tabindex="-1" autocomplete="off">
                        <div class="form-group" style="margin-bottom: 12px;">
                            <textarea id="inquiry-text" name="topic" rows="3" class="form-control" placeholder="What question or topic should Charles examine next?" maxlength="500" required style="resize: vertical; font-size: 0.95em; border-radius: 4px; border: 1px solid #cbd5e0;"></textarea>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <span id="inquiry-status" style="font-size: 0.9em; font-weight: 500;"></span>
                            <button type="submit" id="inquiry-submit-btn" class="btn btn-primary" style="padding: 6px 18px; font-weight: 600; font-size: 0.9em;">
                                <i class="fa fa-paper-plane" style="margin-right: 4px;"></i> Send to Chas
                            </button>
                        </div>
                    </form>
                </div>

                <script>
                function submitInquiry(e) {
                    e.preventDefault();
                    var form = document.getElementById('author-inquiry-form');
                    var btn = document.getElementById('inquiry-submit-btn');
                    var status = document.getElementById('inquiry-status');
                    var textarea = document.getElementById('inquiry-text');
                    var topic = textarea.value.trim();
                    
                    if (!topic) return;
                    
                    btn.disabled = true;
                    status.style.color = '#718096';
                    status.textContent = 'Submitting...';
                    
                    var formData = new FormData(form);
                    
                    fetch('/blog/author-inquiry', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        if (data.status === 'success') {
                            status.style.color = '#2f855a';
                            status.innerHTML = '<i class="fa fa-check-circle"></i> Submitted to Chas. Thank you.';
                            textarea.value = '';
                            textarea.disabled = true;
                            btn.style.display = 'none';
                        } else {
                            btn.disabled = false;
                            status.style.color = '#c53030';
                            status.textContent = data.message || 'Error submitting suggestion.';
                        }
                    })
                    .catch(function(err) {
                        btn.disabled = false;
                        status.style.color = '#c53030';
                        status.textContent = 'Network error. Please try again.';
                    });
                }
                </script>

                <h3><?php echo i18n("Post_by_author");?></h3>
                <?php if (!empty($posts)) { ?>
                    <ul class="post-list">
                        <?php foreach ($posts as $p): ?>
                            <li class="item">
                                <span><a href="<?php echo $p->url ?>"><?php echo $p->title ?></a></span> -
                                <span><?php echo format_date($p->date) ?></span> - <?php echo i18n("Posted_in");?> <span><?php echo $p->category ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php } else {
                    echo i18n('No_posts_found') . '!';
                } ?>
            </div><!--//item-->
        </div><!--//content--> 
    </div><!--//section-inner-->
</section><!--//section-->
<?php if (!empty($posts)) { ?>
<?php if (!empty($pagination['prev']) || !empty($pagination['next'])): ?>
	<div class="pagination"><?php echo $pagination['html'];?></div>
<?php endif; ?>
<?php } ?>
