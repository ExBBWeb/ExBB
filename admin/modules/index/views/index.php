<!-- Dashboard icons -->
            <div class="grid_7">
            	<a href="<?php echo $url->module('forums', 'add'); ?>" class="dashboard-module">
                	<img src="<?php echo $template->url('images/Crystal_Clear_write.gif'); ?>" width="64" height="64"  />
                	<span><?php echo $lang->create_forum; ?></span>
                </a>
                
                <!--a href="#" class="dashboard-module">
                	<img src="<?php echo $template->url('images/Crystal_Clear_file.gif'); ?>" width="64" height="64"  />
                	<span>Uploaded files</span>
                </a-->
                
                <a href="<?php echo $url->module('forums'); ?>" class="dashboard-module">
                	<img src="<?php echo $template->url('images/Crystal_Clear_files.gif'); ?>" width="64" height="64"  />
                	<span><?php echo $lang->forums; ?></span>
                </a>

                <a href="<?php echo $url->module('users'); ?>" class="dashboard-module">
                	<img src="<?php echo $template->url('images/Crystal_Clear_user.gif'); ?>" width="64" height="64"  />
                	<span><?php echo $lang->users; ?></span>
                </a>
                
                <a href="<?php echo $url->module('stats'); ?>" class="dashboard-module">
                	<img src="<?php echo $template->url('images/Crystal_Clear_stats.gif'); ?>" width="64" height="64"  />
                	<span><?php echo $lang->stats; ?></span>
                </a>
                
                <a href="<?php echo $url->module('extensions'); ?>" class="dashboard-module">
                	<img src="<?php echo $template->url('images/icon_plugin.png'); ?>" width="64" height="64"  />
                	<span><?php echo $lang->extensions; ?></span>
                </a>
				
                <a href="<?php echo $url->module('groups'); ?>" class="dashboard-module">
                	<img src="<?php echo $template->url('images/icon_groups.jpg'); ?>" width="64" height="64"  />
                	<span><?php echo $lang->users_groups; ?></span>
                </a>

                <a href="<?php echo $url->module('settings'); ?>" class="dashboard-module">
                	<img src="<?php echo $template->url('images/Crystal_Clear_settings.gif'); ?>" width="64" height="64"  />
                	<span><?php echo $lang->settings; ?></span>
                </a>
                <div style="clear: both"></div>
            </div> <!-- End .grid_7 -->
			
            <!-- Account overview -->
            <div class="grid_5">
                <div class="module">
                        <h2><span><?php echo $lang->boad_info; ?></span></h2>
                        
                        <div class="module-body">
                        
                        	<p>
								<strong><?php echo $lang->topics_count; ?> </strong><?php echo $topics; ?><br />
								<strong><?php echo $lang->posts_count; ?> </strong><?php echo $posts; ?><br />
								<strong><?php echo $lang->users_count; ?> </strong><?php echo $users; ?><br />
                                <strong><?php echo $lang->board_start_date; ?> </strong><?php echo $board_start_date; ?><br />
                            </p>
                        
                             <!--div>
                                 <div class="indicator">
                                     <div style="width: 23%;"></div>
                                 </div>
                                 <p>Your storage space: 23 MB out of 100MB</p>
                             </div>
                             
                             <div>
                                 <div class="indicator">
                                     <div style="width: 100%;"></div>
                                 </div>
                                 <p>Your bandwidth (January): 1 GB out of 1 GB</p>
                             </div>
                             
                        	<p>
                                Need to switch to a bigger plan?<br />
                                <a href="">click here</a><br />
                            </p-->

                        </div>
                </div>
                <div style="clear:both;"></div>
            </div> <!-- End .grid_5 -->