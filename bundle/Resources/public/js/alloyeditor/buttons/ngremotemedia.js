import PropTypes from 'prop-types';
import AlloyEditor from 'alloyeditor';
import EzButton
  from '../../../../../../../../../vendor/ezsystems/ezplatform-admin-ui/src/bundle/Resources/public/js/alloyeditor/src/base/ez-button.js';

export default class BtnNgRemoteMedia extends EzButton {
  static get key() {
    return 'ngremotemedia';
  }

  InsertMedia(data) {
    this.execCommand(data);
  }

  render() {
    const title = 'Netgen Remote Media';

    return (
      <button
        className="ae-button ez-btn-ae ez-btn-ae--ngremotemedia"
        onClick={this.InsertMedia.bind(this)}
        tabIndex={this.props.tabIndex}
        title={title}>
        <svg className="ez-icon ez-btn-ae__icon">
          <use xlinkHref="/bundles/ezplatformadminui/img/ez-icons.svg#file-video" />
        </svg>
      </button>
    );
  }
}

AlloyEditor.Buttons[BtnNgRemoteMedia.key] = AlloyEditor.BtnNgRemoteMedia = BtnNgRemoteMedia;
eZ.addConfig('ezAlloyEditor.BtnNgRemoteMedia', BtnNgRemoteMedia);

BtnNgRemoteMedia.propTypes = {
  command: PropTypes.string,
};

BtnNgRemoteMedia.defaultProps = {
  command: 'InsertMedia',
};
