


<!-- Position Apart -->
<section class="position-apart" style="background: url('{{isset($position_apart_section['single']['media']->background_image)?getFile($position_apart_section['single']['media']->background_image->driver,$position_apart_section['single']['media']->background_image->path):getFile('local','image')}}') no-repeat">
    <!-- <div class="position-apart-image">
        <img src="{{isset($position_apart_section['single']['media']->image)?getFile($position_apart_section['single']['media']->image->driver,$position_apart_section['single']['media']->image->path):getFile('local','image')}}" alt="image">
    </div> -->
    <div class="position-dot-box">
        <img src="{{asset($themeTrue.'images/shape/dot-box-1.png')}}" alt="shape">
    </div>
    <div class="position-line-shape">
        <img src="{{asset($themeTrue.'images/shape/line-shape-2.png')}}" alt="shape">
    </div>
    <div class="container">
        <div class="common-title-container">
            <div class="common-title">
                <h3>HOW TO INVEST</h3>
                <!-- <h3>{!! styleSentence($position_apart_section['single']['heading']??null , 3) !!}</h3>
                <p>{!! $position_apart_section['single']['short_description'] !!}</p>
                <div class="common-title-top-left">
                    <img src="{{asset($themeTrue.'images/shape/title-top-left-2.png')}}" alt="shape">
                </div> -->
            </div>
        </div>
        
        <div class="row">
            <div class="col-lg-3">
                <div class="position-apart-left-container">
                    <div class="position-apart-content">
                            <div class="content">
                                <a href="#">SIGN UP</a>
                                <p>Our team of seasoned experts brings years of experience and deep knowledge in agricultural investments, ensuring your investments are in capable hands.</p>

                            </div>
                        </div>
                </div>
            </div>
              <div class="col-lg-3">
                <div class="position-apart-left-container">
                    <div class="position-apart-content">
                            <div class="content">
                                <a href="#">BROWSE INVESTMENTS</a>
                                <p>Our team of seasoned experts brings years of experience and deep knowledge in agricultural investments, ensuring your investments are in capable hands.</p>
                            </div>
                        </div>
                </div>
            </div>
              <div class="col-lg-3">
                <div class="position-apart-left-container">
                    <div class="position-apart-content">
                            <div class="content">
                                <a href="#">MAKE AN INVESTMENT</a>
                                <p>Our team of seasoned experts brings years of experience and deep knowledge in agricultural investments, ensuring your investments are in capable hands.</p>
                            </div>
                        </div>
                </div>
            </div>
             <div class="col-lg-3">
                <div class="position-apart-left-container">
                    <div class="position-apart-content">
                            <div class="content">
                                <a href="#">HOLD OR SELL</a>
                                <p>Our team of seasoned experts brings years of experience and deep knowledge in agricultural investments, ensuring your investments are in capable hands.</p>
                            </div>
                        </div>
                </div>
            </div>
        </div>
    </div>
</section>
<!-- Position Apart -->
